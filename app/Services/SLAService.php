<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\SlaFilter;
use App\Models\Ajuan;
use App\Models\Layanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

final class SLAService
{
    /**
     * KPI Global SLA
     */
    public function getKpi(array $filters): array
    {
        $requestParams = request()->all();
        $cacheKey = 'sla:kpi:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filters) {
            $query = Ajuan::query()->from('ajuan');
            $filter = new SlaFilter($filters);
            $query = $filter->apply($query);

            $statusSelesai = AjuanStatus::getStatusSelesai();
            $targetSlaJam = config('sla.default_jam', 6);
            $targetSlaMenit = $targetSlaJam * 60; // 360 menit

            $kpiGlobal = (clone $query)->whereIn('ajuan_status', $statusSelesai)
                ->select(
                    DB::raw('COUNT(ajuan_id) as total_ajuan'),
                    DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) <= {$targetSlaMenit} THEN 1 ELSE 0 END) as total_memenuhi"),
                    DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime)) as rata_rata_menit')
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
                'target_sla' => $targetSlaJam,
                'jumlah_ajuan' => $totalAjuan,
            ];
        });
    }

    /**
     * Mendapatkan data SLA.
     * Menggabungkan struktur output Falah dengan optimasi query SQL Amru.
     */
    public function index(array $filters): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 5);

        // -- KODE AMRU (Base Query) -- //
        $query = Ajuan::query()->from('ajuan');
        
        $filter = new SlaFilter($filters);
        $query = $filter->apply($query);

        $statusSelesai = AjuanStatus::getStatusSelesai();
        $targetSlaMenit = config('sla.default_jam', 6) * 60; // 360 menit
        $targetSlaJam = config('sla.default_jam', 6);

        // -- KODE AMRU (Optimasi Agregasi Global, menggantikan iterasi Falah) -- //
        $kpiGlobal = (clone $query)->whereIn('ajuan_status', $statusSelesai)
            ->select(
                DB::raw('COUNT(ajuan_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) <= {$targetSlaMenit} THEN 1 ELSE 0 END) as total_memenuhi"),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime)) as rata_rata_menit')
            )->first();

        $averageProcessTime = round((float) ($kpiGlobal->rata_rata_menit ?? 0) / 60, 1);
        
        $totalAjuan = (int) ($kpiGlobal->total_ajuan ?? 0);
        $totalMemenuhi = (int) ($kpiGlobal->total_memenuhi ?? 0);
        
        // -- KODE FALAH (Logika Pencapaian SLA) -- //
        $slaAchievement = $totalAjuan > 0 ? round(($totalMemenuhi / $totalAjuan) * 100) : 0;

        // -- KODE FALAH (Detail per layanan) -- //
        $details = [];

        // KODE FALAH: Total Ajuan Row
        $details[] = [
            'id' => 1,
            'jenis_layanan' => 'TOTAL AJUAN',
            'jumlah_ajuan' => $totalAjuan,
            'rata_rata_waktu' => $averageProcessTime,
        ];

        // -- KODE AMRU (Optimasi: Ambil semua layanan, lalu fetch agregat 1x query untuk menghindari N+1) -- //
        $layanans = Layanan::query()->orderBy('layanan_pos')->get();
        
        $agregatLayanan = (clone $query)->whereIn('ajuan_status', $statusSelesai)
            ->select(
                'ajuan_layanan_kode',
                DB::raw('COUNT(ajuan_id) as jumlah_ajuan'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime)) as rata_rata_menit')
            )
            ->groupBy('ajuan_layanan_kode')
            ->get()
            ->keyBy('ajuan_layanan_kode');

        foreach ($layanans as $index => $layanan) {
            $agregat = $agregatLayanan->get($layanan->layanan_kode);
            $jml = $agregat ? (int) $agregat->jumlah_ajuan : 0;
            $rataMenit = $agregat ? (float) $agregat->rata_rata_menit : 0;

            $details[] = [
                'id' => $index + 2,
                'jenis_layanan' => strtoupper($layanan->layanan_nama),
                'jumlah_ajuan' => $jml,
                'rata_rata_waktu' => round($rataMenit / 60, 1),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting & Manual Pagination (KODE FALAH)
        |--------------------------------------------------------------------------
        */
        $detailsCollection = collect($details);

        match ($filters['sort_by'] ?? 'newest') {
            'oldest' => $detailsCollection = $detailsCollection->sortBy('rata_rata_waktu')->values(),
            default => $detailsCollection = $detailsCollection->sortByDesc('rata_rata_waktu')->values(),
        };

        $total = $detailsCollection->count();
        $list = $detailsCollection->forPage($page, $perPage)->values();

        return [
            'rata_rata_waktu_proses' => $averageProcessTime,
            'pencapaian_sla' => $slaAchievement,
            'target_sla' => $targetSlaJam,
            'jumlah_ajuan' => $totalAjuan,
            'daftar_rincian' => [
                'list' => $list->toArray(),
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_page' => (int) ceil($total / $perPage),
                ],
            ],
        ];
    }

    /**
     * -- TAMBAHAN AMRU (Untuk Endpoint Export) --
     */
    public function export(array $filters): array
    {
        // Export mengambil semua data layanan tanpa paginasi 
        // dengan format data yang disesuaikan export.
        $query = Ajuan::query()->from('ajuan');
        
        $filter = new SlaFilter($filters);
        $query = $filter->apply($query);

        $statusSelesai = AjuanStatus::getStatusSelesai();

        $data = (clone $query)->join('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
            ->whereIn('ajuan.ajuan_status', $statusSelesai)
            ->select(
                'layanan.layanan_kode',
                'layanan.layanan_nama',
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime)) as rata_rata_menit')
            )
            ->groupBy('layanan.layanan_kode', 'layanan.layanan_nama')
            ->get();

        $defaultSla = config('sla.default_jam', 6) * 60;
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
        })->toArray();
    }
}