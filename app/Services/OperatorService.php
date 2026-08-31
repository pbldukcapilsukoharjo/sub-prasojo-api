<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Prasojo\Admin;
use App\Models\Prasojo\Ajuan;
use App\Models\Prasojo\LogAjuanStatus;
use App\Filters\OperatorFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperatorService
{
    /**
     * Get global KPI statistics for operators.
     */
    public function getKpiGlobal(OperatorFilter $filter): array
    {
        try {
            $query = Admin::query()
                ->where('admin.level', Admin::LEVEL_OPERATOR)
                ->leftJoin('log_ajuan_status', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
                ->leftJoin('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');

            $query = $filter->apply($query);

            $defaultDb = config('database.connections.mysql.database');
            $query->leftJoin(DB::raw("`{$defaultDb}`.`ajuan_sla_summaries` as sla_summary"), 'sla_summary.ajuan_id', '=', 'ajuan.ajuan_id');

            $stats = $query->select(
                DB::raw('COUNT(log_ajuan_status.log_id) as total_layanan'),
                DB::raw("SUM(CASE WHEN UPPER(log_ajuan_status.log_status) = 'SELESAI DIPROSES' THEN 1 ELSE 0 END) as total_selesai"),
                DB::raw("AVG(CASE WHEN UPPER(log_ajuan_status.log_status) = 'SELESAI DIPROSES' THEN sla_summary.durasi_sla_menit ELSE NULL END) as rata_rata_durasi_menit")
            )->first();

            $totalLayanan = (int) ($stats->total_layanan ?? 0);
            $totalSelesai = (int) ($stats->total_selesai ?? 0);
            $rataRataDurasiMenit = (float) ($stats->rata_rata_durasi_menit ?? 0);
            $rataRataDurasi = $totalLayanan > 0 ? round($rataRataDurasiMenit / 60, 1) : 0;
            $tingkatSelesai = $totalLayanan > 0 ? round(($totalSelesai / $totalLayanan) * 100, 1) : 0;

            return [
                'total_ajuan' => $totalLayanan,
                'total_selesai' => $totalSelesai,
                'tingkat_selesai' => $tingkatSelesai,
                'rata_rata_durasi' => $rataRataDurasi,
            ];
        } catch (\Throwable $e) {
            Log::error('[OperatorService@getKpiGlobal] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get operator ranking list with pagination.
     */
    public function getRanking(OperatorFilter $filter, int $perPage = 10): LengthAwarePaginator
    {
        try {
            $query = Admin::query()
                ->where('admin.level', Admin::LEVEL_OPERATOR)
                ->leftJoin('log_ajuan_status', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
                ->leftJoin('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');

            $query = $filter->apply($query);

            $sort_by = request('sort', 'newest');
            $sortDirection = $sort_by === 'oldest' ? 'asc' : 'desc';

            $query->select(
                'admin.id',
                'admin.fullname as operator',
                DB::raw('MAX(ajuan.ajuan_kelurahan_name) as desa'),
                DB::raw('MAX(ajuan.ajuan_kecamatan_name) as kecamatan'),
                DB::raw('COUNT(log_ajuan_status.log_id) as jumlah_ajuan')
            )
            ->groupBy('admin.id', 'admin.fullname')
            ->orderBy('jumlah_ajuan', $sortDirection);

            $paginator = $query->paginate($perPage);

            $page = $paginator->currentPage();
            $offset = ($page - 1) * $perPage;

            $paginator->getCollection()->transform(function ($item, $index) use ($offset) {
                return [
                    'id' => $item->id,
                    'peringkat' => $offset + $index + 1,
                    'operator' => $item->operator,
                    'desa' => $item->desa ?? '-',
                    'kecamatan' => $item->kecamatan ?? '-',
                    'jumlah_ajuan' => (int) $item->jumlah_ajuan,
                ];
            });

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[OperatorService@getRanking] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Build ranking query for export.
     */
    public function buildRankingQuery(OperatorFilter $filter): Builder
    {
        $query = Admin::query()
            ->where('admin.level', Admin::LEVEL_OPERATOR)
            ->leftJoin('log_ajuan_status', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
            ->leftJoin('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');

        $query = $filter->apply($query);

        $defaultDb = config('database.connections.mysql.database');
        $query->leftJoin(DB::raw("`{$defaultDb}`.`ajuan_sla_summaries` as sla_summary"), 'sla_summary.ajuan_id', '=', 'ajuan.ajuan_id');

        return $query->select(
            'admin.id as id_operator',
            'admin.fullname as nama',
            DB::raw('COUNT(log_ajuan_status.log_id) as total_berkas'),
            DB::raw("AVG(CASE WHEN UPPER(log_ajuan_status.log_status) = 'SELESAI DIPROSES' THEN sla_summary.durasi_sla_menit ELSE NULL END) as rata_rata_waktu_menit")
        )
        ->groupBy('admin.id', 'admin.fullname')
        ->orderBy('total_berkas', 'desc');
    }

    /**
     * Get individual operator detail KPI.
     */
    public function getDetailKpi(int $operatorId, array $filters): array
    {
        try {
            $operator = Admin::query()
                ->where('level', Admin::LEVEL_OPERATOR)
                ->findOrFail($operatorId);

            $statsQuery = LogAjuanStatus::query()
                ->where('log_admin_id', $operatorId);

            if (!empty($filters['tahun'])) {
                $statsQuery->whereYear('log_create_datetime', (int) $filters['tahun']);
            }
            if (!empty($filters['periode_bulan'])) {
                $statsQuery->whereMonth('log_create_datetime', (int) $filters['periode_bulan']);
            }
            if (!empty($filters['id_layanan'])) {
                $layananKode = \App\Models\Prasojo\Layanan::resolveKode($filters['id_layanan']);
                $statsQuery->join('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id')
                           ->where('ajuan.ajuan_layanan_kode', $layananKode);
            }

            $pelapor = $filters['pelapor'] ?? $filters['reporter'] ?? $filters['id_pelapor'] ?? null;
            if (!empty($pelapor)) {
                if (empty($filters['id_layanan'])) {
                    $statsQuery->join('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');
                }
                
                $pelaporLower = strtolower($pelapor);
                if ($pelaporLower === 'online') {
                    $statsQuery->where('ajuan.ajuan_is_online', 1);
                } elseif ($pelaporLower === 'offline') {
                    $statsQuery->where('ajuan.ajuan_is_online', 0);
                } elseif ($pelaporLower === 'mandiri') {
                    $statsQuery->where('ajuan.ajuan_is_mandiri', 1);
                } elseif ($pelaporLower === 'operator') {
                    $statsQuery->where('ajuan.ajuan_is_mandiri', 0);
                } elseif ($pelaporLower === 'tamat') {
                    $statsQuery->whereRaw('UPPER(TRIM(ajuan.ajuan_keterangan)) = ?', ['TAMAT']);
                } else {
                    $statsQuery->where('ajuan.ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
                }
            }

            $stats = $statsQuery->select(
                DB::raw('COUNT(log_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN UPPER(log_status) = 'SELESAI DIPROSES' THEN 1 ELSE 0 END) as total_selesai")
            )->first();

            $totalAjuan = (int) ($stats->total_ajuan ?? 0);
            $totalSelesai = (int) ($stats->total_selesai ?? 0);
            $tingkatSelesai = $totalAjuan > 0 ? (int) round(($totalSelesai / $totalAjuan) * 100) : 0;

            // Monthly chart
            $bulan = [
                'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0,
                'Mei' => 0, 'Jun' => 0, 'Jul' => 0, 'Agu' => 0,
                'Sep' => 0, 'Okt' => 0, 'Nov' => 0, 'Des' => 0,
            ];
            
            $monthsMap = array_keys($bulan);

            $chartQuery = LogAjuanStatus::query()
                ->where('log_admin_id', $operatorId)
                ->whereNotNull('log_create_datetime');

            if (!empty($filters['tahun'])) {
                $chartQuery->whereYear('log_create_datetime', (int) $filters['tahun']);
            } else {
                $chartQuery->whereYear('log_create_datetime', Carbon::now()->year);
            }

            if (!empty($filters['id_layanan'])) {
                $layananKode = \App\Models\Prasojo\Layanan::resolveKode($filters['id_layanan']);
                $chartQuery->join('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id')
                           ->where('ajuan.ajuan_layanan_kode', $layananKode);
            }

            if (!empty($pelapor)) {
                if (empty($filters['id_layanan'])) {
                    $chartQuery->join('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');
                }
                
                $pelaporLower = strtolower($pelapor);
                if ($pelaporLower === 'online') {
                    $chartQuery->where('ajuan.ajuan_is_online', 1);
                } elseif ($pelaporLower === 'offline') {
                    $chartQuery->where('ajuan.ajuan_is_online', 0);
                } elseif ($pelaporLower === 'mandiri') {
                    $chartQuery->where('ajuan.ajuan_is_mandiri', 1);
                } elseif ($pelaporLower === 'operator') {
                    $chartQuery->where('ajuan.ajuan_is_mandiri', 0);
                } elseif ($pelaporLower === 'tamat') {
                    $chartQuery->whereRaw('UPPER(TRIM(ajuan.ajuan_keterangan)) = ?', ['TAMAT']);
                } else {
                    $chartQuery->where('ajuan.ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
                }
            }

            $chartData = $chartQuery->select(
                DB::raw('MONTH(log_create_datetime) as bulan_num'),
                DB::raw('COUNT(log_id) as total')
            )
            ->groupBy('bulan_num')
            ->get();

            foreach ($chartData as $item) {
                if ($item->bulan_num >= 1 && $item->bulan_num <= 12) {
                    $bulan[$monthsMap[$item->bulan_num - 1]] = (int) $item->total;
                }
            }

            return [
                'id' => $operator->id,
                'nama' => $operator->fullname,
                'total_ajuan' => $totalAjuan,
                'total_selesai' => $totalSelesai,
                'tingkat_selesai' => $tingkatSelesai,
                'layanan_perbulan' => $bulan,
            ];
        } catch (\Throwable $e) {
            Log::error('[OperatorService@getDetailKpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get paginated riwayat/history for a specific operator.
     */
    public function getRiwayat(int $operatorId, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        try {
            $operator = Admin::query()
                ->where('level', Admin::LEVEL_OPERATOR)
                ->findOrFail($operatorId);

            $query = $operator->logAjuanStatuses()
                ->with(['ajuan.pelapor'])
                ->whereHas('ajuan');

            if (!empty($filters['tahun'])) {
                $query->whereYear('log_create_datetime', (int) $filters['tahun']);
            }
            if (!empty($filters['periode_bulan'])) {
                $query->whereMonth('log_create_datetime', (int) $filters['periode_bulan']);
            }
            if (!empty($filters['id_layanan'])) {
                $layananKode = \App\Models\Prasojo\Layanan::resolveKode($filters['id_layanan']);
                $query->whereHas('ajuan', function($q) use ($layananKode) {
                    $q->where('ajuan_layanan_kode', $layananKode);
                });
            }
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->whereHas('ajuan', function($q) use ($search) {
                    $q->where('ajuan_no_reg', 'like', "%{$search}%")
                      ->orWhere('ajuan_pelapor_nik', 'like', "%{$search}%");
                });
            }

            $pelapor = $filters['pelapor'] ?? $filters['reporter'] ?? $filters['id_pelapor'] ?? null;
            if (!empty($pelapor)) {
                $query->whereHas('ajuan', function ($q) use ($pelapor) {
                    $pelaporLower = strtolower($pelapor);
                    if ($pelaporLower === 'online') {
                        $q->where('ajuan_is_online', 1);
                    } elseif ($pelaporLower === 'offline') {
                        $q->where('ajuan_is_online', 0);
                    } elseif ($pelaporLower === 'mandiri') {
                        $q->where('ajuan_is_mandiri', 1);
                    } elseif ($pelaporLower === 'operator') {
                        $q->where('ajuan_is_mandiri', 0);
                    } elseif ($pelaporLower === 'tamat') {
                        $q->whereRaw('UPPER(TRIM(ajuan_keterangan)) = ?', ['TAMAT']);
                    } else {
                        $q->where('ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
                    }
                });
            }

            $paginator = $query->orderByDesc('log_create_datetime')->paginate($perPage);

            $paginator->getCollection()->transform(function ($log) {
                $ajuan = $log->ajuan;
                return [
                    'id' => $log->log_id,
                    'no_regis' => $ajuan->ajuan_no_reg,
                    'pemohon' => $ajuan->pelapor?->fullname ?? 'Unknown',
                    'kode_ajuan' => $ajuan->ajuan_layanan_kode,
                    'desa' => $ajuan->ajuan_kelurahan_name,
                    'tanggal' => $log->log_create_datetime ? Carbon::parse($log->log_create_datetime)->format('d-m-Y') : null,
                    'waktu' => $log->log_create_datetime ? Carbon::parse($log->log_create_datetime)->format('H:i') : null,
                    'status' => $log->log_status,
                ];
            });

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[OperatorService@getRiwayat] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


}
