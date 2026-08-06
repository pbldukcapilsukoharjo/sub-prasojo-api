<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\SLAFilter;
use App\Models\Prasojo\Ajuan;
use App\Models\Prasojo\Layanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SLAService
{
    /**
     * KPI Global SLA
     */
    public function getKpi(array $filters): array
    {
        try {
            $requestParams = request()->all();
            $userId = request()->attributes->get('auth_user_id');
            
            $user = $userId ? \App\Models\Monitoring\SubUser::find($userId) : null;
            $defaultJam = config('sla.default_jam', 6);
            $targetSlaMenit = $defaultJam * 60; // default 360 menit
            $targetSlaText = "{$defaultJam} Jam";
            
            if ($user && $user->sla_target_value && $user->sla_target_unit) {
                if ($user->sla_target_unit === 'menit') {
                    $targetSlaMenit = $user->sla_target_value;
                } elseif ($user->sla_target_unit === 'jam') {
                    $targetSlaMenit = $user->sla_target_value * 60;
                } elseif ($user->sla_target_unit === 'hari') {
                    $targetSlaMenit = $user->sla_target_value * 1440;
                }
                $targetSlaText = $user->sla_target_value . " " . ucfirst($user->sla_target_unit);
            }

            $cacheKey = 'sla:kpi:' . md5(json_encode($requestParams) . ':' . $userId . ':' . $targetSlaMenit);

            return Cache::remember($cacheKey, 600, function () use ($filters, $targetSlaMenit, $targetSlaText) {
                $query = Ajuan::query()->from('ajuan');
                $filter = new SLAFilter($filters);
                $query = $filter->apply($query);

                // Terapkan subquery SLA
                $query = $this->applyLogSummarySubquery($query);

                if (!empty($filters['max_sla_minutes'])) {
                    $query->where('sla_summary.durasi_sla_menit', '<=', (int) $filters['max_sla_minutes']);
                }

                if (!empty($filters['operator_id'])) {
                    $query->where('sla_summary.operator_user_id', $filters['operator_id']);
                }

                $statusSelesai = AjuanStatus::getStatusSelesai();

                $kpiGlobal = (clone $query)->whereIn('ajuan_status', $statusSelesai)
                    ->select(
                        DB::raw('COUNT(ajuan.ajuan_id) as total_ajuan'),
                        DB::raw("SUM(CASE WHEN sla_summary.durasi_sla_menit <= {$targetSlaMenit} THEN 1 ELSE 0 END) as total_memenuhi"),
                        DB::raw('AVG(sla_summary.durasi_sla_menit) as rata_rata_menit')
                    )->first();

                $rataRataMenit = (float)($kpiGlobal->rata_rata_menit ?? 0);
                $totalAjuan = (int)($kpiGlobal->total_ajuan ?? 0);
                $totalMemenuhi = (int)($kpiGlobal->total_memenuhi ?? 0);
                
                $capaianPersen = $totalAjuan > 0 ? round(($totalMemenuhi / $totalAjuan) * 100, 2) : 0.0;

                $jam = floor($rataRataMenit / 60);
                $menit = round($rataRataMenit % 60);
                $slaText = "";
                if ($jam > 0) {
                    $slaText .= $jam . " Jam ";
                }
                $slaText .= $menit . " Menit";
                $slaText = trim($slaText);
                if ($slaText === "0 Menit" || $slaText === "") {
                    $slaText = "0 Menit";
                }

                return [
                    'rata_rata_global_text' => $slaText,
                    'capaian_sla_persen' => $capaianPersen,
                    'target_sla' => $targetSlaText,
                    'jumlah_ajuan' => $totalAjuan,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('[SLAService@getKpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Mendapatkan data SLA.
     * Menggabungkan struktur output Falah dengan optimasi query SQL Amru.
     * 
     * @param array $filters
     * @return array{list: array, meta: array}
     */
    public function index(array $filters): array
    {
        try {
            $page = (int) ($filters['page'] ?? 1);
            $perPage = (int) ($filters['per_page'] ?? 5);

            // -- KODE AMRU (Base Query) -- //
            $query = Ajuan::query()->from('ajuan');
            
            $filter = new SLAFilter($filters);
            $query = $filter->apply($query);

            // Terapkan subquery SLA
            $query = $this->applyLogSummarySubquery($query);

            if (!empty($filters['max_sla_minutes'])) {
                $query->where('sla_summary.durasi_sla_menit', '<=', (int) $filters['max_sla_minutes']);
            }

            if (!empty($filters['operator_id'])) {
                $query->where('sla_summary.operator_user_id', $filters['operator_id']);
            }

            $statusSelesai = AjuanStatus::getStatusSelesai();
            
            $user = auth()->user();
            $targetSlaMenit = config('sla.default_jam', 6) * 60; // 360 menit
            $targetSlaJam = config('sla.default_jam', 6);
            
            if ($user && $user->sla_target_value && $user->sla_target_unit) {
                if ($user->sla_target_unit === 'menit') {
                    $targetSlaMenit = $user->sla_target_value;
                } elseif ($user->sla_target_unit === 'jam') {
                    $targetSlaMenit = $user->sla_target_value * 60;
                } elseif ($user->sla_target_unit === 'hari') {
                    $targetSlaMenit = $user->sla_target_value * 1440;
                }
                $targetSlaJam = round($targetSlaMenit / 60, 2);
            }

            // -- KODE FALAH (Detail per layanan) -- //
            $details = [];


            // -- KODE AMRU (Optimasi: Ambil semua layanan, lalu fetch agregat 1x query untuk menghindari N+1) -- //
            $layanansQuery = Layanan::query()->orderBy('layanan_pos');
            
            if (!empty($filters['search'])) {
                $search = strtolower($filters['search']);
                $layanansQuery->whereRaw('LOWER(layanan_nama) LIKE ?', ["%{$search}%"]);
            }

            if (!empty($filters['id_layanan'])) {
                $layanansQuery->where('layanan_kode', $filters['id_layanan']);
            }

            $layanans = $layanansQuery->get();
            
            $agregatLayanan = (clone $query)->whereIn('ajuan.ajuan_status', $statusSelesai)
                ->select(
                    'ajuan.ajuan_layanan_kode',
                    DB::raw('COUNT(ajuan.ajuan_id) as jumlah_ajuan'),
                    DB::raw('AVG(sla_summary.durasi_sla_menit) as rata_rata_menit')
                )
                ->groupBy('ajuan.ajuan_layanan_kode')
                ->get()
                ->keyBy('ajuan_layanan_kode');

            foreach ($layanans as $index => $layanan) {
                $agregat = $agregatLayanan->get($layanan->layanan_kode);
                $jml = $agregat ? (int) $agregat->jumlah_ajuan : 0;
                $rataMenit = $agregat ? (float) $agregat->rata_rata_menit : 0;

                if (!empty($filters['pelapor']) && $jml === 0) {
                    continue;
                }

                $jam = floor($rataMenit / 60);
                $menit = round($rataMenit % 60);
                $waktuText = "";
                if ($jam > 0) {
                    $waktuText .= $jam . " Jam ";
                }
                $waktuText .= $menit . " Menit";
                $waktuText = trim($waktuText);
                if ($waktuText === "0 Menit" || $waktuText === "") {
                    $waktuText = "0 Menit";
                }

                $details[] = [
                    'id' => $index + 2,
                    'jenis_layanan' => strtoupper($layanan->layanan_nama),
                    'jumlah_ajuan' => $jml,
                    'rata_rata_menit' => $rataMenit,
                    'rata_rata_waktu' => $waktuText,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Sorting & Manual Pagination (KODE FALAH)
            |--------------------------------------------------------------------------
            |
            */
            /** @var \Illuminate\Support\Collection $detailsCollection */
            $detailsCollection = collect($details);

            if (($filters['sort_by'] ?? 'newest') === 'oldest') {
                $sortedCollection = $detailsCollection->sortBy('rata_rata_menit')->values();
            } else {
                $sortedCollection = $detailsCollection->sortByDesc('rata_rata_menit')->values();
            }

            $total = $sortedCollection->count();
            
            /** @var \Illuminate\Support\Collection $list */
            $list = $sortedCollection->forPage($page, $perPage)->map(function($item) {
                unset($item['rata_rata_menit']);
                return $item;
            })->values();

            return [
                'list' => $list->toArray(),
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_page' => (int) ceil($total / $perPage),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('[SLAService@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * -- TAMBAHAN AMRU (Untuk Endpoint Export) --
     */
    public function export(array $filters): \Illuminate\Support\Collection
    {
        try {
            // Export mengambil semua data layanan tanpa paginasi 
            // dengan format data yang disesuaikan export.
            $query = Ajuan::query()->from('ajuan');
            
            $filter = new SLAFilter($filters);
            $query = $filter->apply($query);

            // Terapkan subquery SLA
            $query = $this->applyLogSummarySubquery($query);

            if (!empty($filters['max_sla_minutes'])) {
                $query->where('sla_summary.durasi_sla_menit', '<=', (int) $filters['max_sla_minutes']);
            }

            if (!empty($filters['operator_id'])) {
                $query->where('sla_summary.operator_user_id', $filters['operator_id']);
            }

            $statusSelesai = AjuanStatus::getStatusSelesai();

            $data = (clone $query)->join('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
                ->whereIn('ajuan.ajuan_status', $statusSelesai)
                ->select(
                    'layanan.layanan_kode',
                    'layanan.layanan_nama',
                    DB::raw('AVG(sla_summary.durasi_sla_menit) as rata_rata_menit')
                )
                ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
                ->get();

            $user = auth()->user();
            $defaultSla = config('sla.default_jam', 6) * 60;
            
            if ($user && $user->sla_target_value && $user->sla_target_unit) {
                if ($user->sla_target_unit === 'menit') {
                    $defaultSla = $user->sla_target_value;
                } elseif ($user->sla_target_unit === 'jam') {
                    $defaultSla = $user->sla_target_value * 60;
                } elseif ($user->sla_target_unit === 'hari') {
                    $defaultSla = $user->sla_target_value * 1440;
                }
            }
            
            $perLayanan = config('sla.per_layanan', []);

            return $data->map(function ($item) use ($defaultSla, $perLayanan) {
                $rataRataMenit = (float)$item->rata_rata_menit;
                $jamAktual = floor($rataRataMenit / 60);
                $menitAktual = round($rataRataMenit % 60);
                $aktualText = "";
                if ($jamAktual > 0) $aktualText .= $jamAktual . " Jam ";
                $aktualText .= $menitAktual . " Menit";
                
                $targetMenit = isset($perLayanan[$item->layanan_kode]) 
                    ? $perLayanan[$item->layanan_kode] * 60 
                    : $defaultSla;

                $statusSla = $rataRataMenit <= $targetMenit ? 'MEMENUHI' : 'TIDAK MEMENUHI';
                
                $targetJam = floor($targetMenit / 60);
                $targetMenitSisa = $targetMenit % 60;
                $targetText = "";
                if ($targetJam > 0) $targetText .= $targetJam . " Jam ";
                if ($targetMenitSisa > 0) $targetText .= $targetMenitSisa . " Menit";

                return [
                    'layanan_kode' => $item->layanan_kode,
                    'nama_layanan' => $item->layanan_nama,
                    'target_sla' => trim($targetText),
                    'aktual_rata_rata' => trim($aktualText),
                    'status_sla' => $statusSla,
                ];
            })->values();
        } catch (\Throwable $e) {
            Log::error('[SLAService@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Terapkan subquery untuk menghitung waktu mulai dan waktu selesai dari tabel log_ajuan_status
     */
    protected function applyLogSummarySubquery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $defaultDb = config('database.connections.mysql.database');
        
        return $query->join(DB::raw("`{$defaultDb}`.`ajuan_sla_summaries` as sla_summary"), 'sla_summary.ajuan_id', '=', 'ajuan.ajuan_id');
    }

    /**
     * Update target SLA for a specific operator.
     */
    public function updateSlaTarget(int|string $operatorId, array $data): array
    {
        try {
            // Kita pakai string type karena id dari sub_users adalah UUID
            $subUser = \App\Models\Monitoring\SubUser::findOrFail($operatorId);

            $subUser->update([
                'sla_target_value' => $data['sla_target_value'],
                'sla_target_unit' => $data['sla_target_unit'],
            ]);

            return [
                'id' => $subUser->id,
                'name' => $subUser->fullname,
                'sla_target_value' => $subUser->sla_target_value,
                'sla_target_unit' => $subUser->sla_target_unit,
            ];
        } catch (\Throwable $e) {
            Log::error('[SLAService@updateSlaTarget] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get Target SLA Operator
     */
    public function getSlaTarget(int|string $operatorId): array
    {
        $user = \App\Models\Monitoring\SubUser::findOrFail($operatorId);

        return [
            'sla_target_value' => $user->sla_target_value ?? config('sla.default_jam', 6),
            'sla_target_unit' => $user->sla_target_unit ?? 'jam',
        ];
    }

    /**
     * Mendapatkan sample data ajuan SLA untuk verifikasi/audit SLA.
     *
     * @param array $filters
     * @return array{list: array, meta: array}
     */
    public function getSamples(array $filters): array
    {
        try {
            $page = (int) ($filters['page'] ?? 1);
            $perPage = (int) ($filters['per_page'] ?? 10);

            $userId = request()->attributes->get('auth_user_id');
            $user = $userId ? \App\Models\Monitoring\SubUser::find($userId) : null;

            $defaultJam = config('sla.default_jam', 6);
            $targetSlaMenit = $defaultJam * 60;

            if ($user && $user->sla_target_value && $user->sla_target_unit) {
                if ($user->sla_target_unit === 'menit') {
                    $targetSlaMenit = $user->sla_target_value;
                } elseif ($user->sla_target_unit === 'jam') {
                    $targetSlaMenit = $user->sla_target_value * 60;
                } elseif ($user->sla_target_unit === 'hari') {
                    $targetSlaMenit = $user->sla_target_value * 1440;
                }
            }

            $query = Ajuan::query()->from('ajuan')
                ->with(['layanan', 'pelapor']);

            // Filter standard via SLAFilter
            $filter = new SLAFilter($filters);
            $query = $filter->apply($query);

            // Join summary SLA (precalculated times & durations)
            $query = $this->applyLogSummarySubquery($query);

            // Hanya ajuan yang sudah selesai
            $statusSelesai = AjuanStatus::getStatusSelesai();
            $query->whereIn('ajuan.ajuan_status', $statusSelesai);

            if (!empty($filters['max_sla_minutes'])) {
                $query->where('sla_summary.durasi_sla_menit', '<=', (int) $filters['max_sla_minutes']);
            }

            if (!empty($filters['operator_id'])) {
                $query->where('sla_summary.operator_user_id', $filters['operator_id']);
            }

            // Pemilihan manual via ajuan_id
            if (!empty($filters['ajuan_id'])) {
                $query->where('ajuan.ajuan_id', $filters['ajuan_id']);
            }

            // Kategori sample
            $kategori = $filters['kategori'] ?? null;
            if ($kategori === 'tercepat') {
                $query->orderBy('sla_summary.durasi_sla_menit', 'asc');
            } elseif ($kategori === 'terlambat') {
                $query->orderBy('sla_summary.durasi_sla_menit', 'desc');
            } elseif ($kategori === 'terbaru') {
                $query->orderBy('ajuan.ajuan_create_datetime', 'desc');
            } elseif ($kategori === '30_hari') {
                $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(30);
                $query->where(function ($q) use ($thirtyDaysAgo) {
                    $q->where('sla_summary.waktu_selesai', '>=', $thirtyDaysAgo)
                      ->orWhere('ajuan.ajuan_create_datetime', '>=', $thirtyDaysAgo);
                })->orderBy('sla_summary.waktu_selesai', 'desc');
            }

            // Explicit sort_by overrides
            if (!empty($filters['sort_by'])) {
                if ($filters['sort_by'] === 'fastest') {
                    $query->orderBy('sla_summary.durasi_sla_menit', 'asc');
                } elseif ($filters['sort_by'] === 'slowest') {
                    $query->orderBy('sla_summary.durasi_sla_menit', 'desc');
                } elseif ($filters['sort_by'] === 'newest') {
                    $query->orderBy('ajuan.ajuan_create_datetime', 'desc');
                } elseif ($filters['sort_by'] === 'oldest') {
                    $query->orderBy('ajuan.ajuan_create_datetime', 'asc');
                }
            } elseif (empty($kategori)) {
                $query->orderBy('ajuan.ajuan_create_datetime', 'desc');
            }

            $query->select(
                'ajuan.*',
                'sla_summary.waktu_mulai',
                'sla_summary.waktu_selesai',
                'sla_summary.durasi_sla_menit',
                'sla_summary.target_sla_menit_aktual',
                'sla_summary.operator_user_id'
            );

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return [
                'list' => $paginator->items(),
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'total_page' => $paginator->lastPage(),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('[SLAService@getSamples] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}