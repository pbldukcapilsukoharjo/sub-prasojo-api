<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\DashboardFilter;
use App\Models\Ajuan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Menghitung nilai tren persentase.
     * Rumus: ((Nilai Saat Ini - Nilai Sebelumnya) / Nilai Sebelumnya) * 100
     */
    private function calculateTrend(float $current, float $previous): float
    {
        if ($previous == 0 && $current > 0) {
            return 100.0;
        }
        if ($previous == 0 && $current == 0) {
            return 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    public function getKpi(DashboardFilter $filter): array
    {
        // Parameter request untuk cache key
        $requestParams = request()->all();
        $cacheKey = 'dashboard:kpi:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filter, $requestParams) {
            // 1. KPI Saat Ini
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";
            $statusDitolak = "'" . implode("','", AjuanStatus::getStatusDitolak()) . "'";

            $kpi = $query->select(
                DB::raw('COUNT(ajuan_id) as total_pengajuan'),
                DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                DB::raw("SUM(CASE WHEN ajuan_status IN ($statusDitolak) THEN 1 ELSE 0 END) as total_ditolak"),
                DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_sla")
            )->first();

            $total_pengajuan = (int) ($kpi->total_pengajuan ?? 0);
            $total_selesai = (int) ($kpi->total_selesai ?? 0);
            $total_ditolak = (int) ($kpi->total_ditolak ?? 0);
            $rata_rata_sla_menit = (float) ($kpi->rata_rata_sla ?? 0);

            // 2. KPI Periode Sebelumnya (Untuk Tren)
            // Sederhananya, kita hitung jika ada periode_bulan atau start_date
            $prevTotalPengajuan = 0;
            $prevTotalSelesai = 0;
            $prevTotalDitolak = 0;
            $prevSlaMenit = 0.0;

            // Jika filter bulan digunakan, kita cari data bulan sebelumnya
            if (!empty($requestParams['periode_bulan'])) {
                $prevMonth = (int) $requestParams['periode_bulan'] - 1;
                $prevYear = date('Y');
                if ($prevMonth === 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }

                $prevQuery = Ajuan::query();
                // Terapkan filter selain tanggal
                if (!empty($requestParams['id_kecamatan'])) {
                    $prevQuery->where('ajuan_kecamatan_code', $requestParams['id_kecamatan']);
                }
                if (!empty($requestParams['id_layanan'])) {
                    $prevQuery->where('ajuan_layanan_kode', $requestParams['id_layanan']);
                }
                $prevQuery->whereMonth('ajuan_create_datetime', $prevMonth)
                          ->whereYear('ajuan_create_datetime', $prevYear);

                $prevKpi = $prevQuery->select(
                    DB::raw('COUNT(ajuan_id) as total_pengajuan'),
                    DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                    DB::raw("SUM(CASE WHEN ajuan_status IN ($statusDitolak) THEN 1 ELSE 0 END) as total_ditolak"),
                    DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_sla")
                )->first();

                $prevTotalPengajuan = (int) ($prevKpi->total_pengajuan ?? 0);
                $prevTotalSelesai = (int) ($prevKpi->total_selesai ?? 0);
                $prevTotalDitolak = (int) ($prevKpi->total_ditolak ?? 0);
                $prevSlaMenit = (float) ($prevKpi->rata_rata_sla ?? 0);
            }

            // Hitung tren
            $trendPengajuan = $this->calculateTrend((float) $total_pengajuan, (float) $prevTotalPengajuan);
            $trendSelesai = $this->calculateTrend((float) $total_selesai, (float) $prevTotalSelesai);
            $trendDitolak = $this->calculateTrend((float) $total_ditolak, (float) $prevTotalDitolak);
            $trendSla = $this->calculateTrend($rata_rata_sla_menit, $prevSlaMenit);

            // Format SLA
            $jam = floor($rata_rata_sla_menit / 60);
            $menit = round($rata_rata_sla_menit % 60);
            $sla_text = "";
            if ($jam > 0) $sla_text .= $jam . " Jam ";
            $sla_text .= $menit . " Menit";
            if (trim($sla_text) === "0 Menit") $sla_text = "0 Menit";

            return [
                'total_pengajuan' => $total_pengajuan,
                'total_pengajuan_trend_persen' => $trendPengajuan,
                'total_selesai' => $total_selesai,
                'total_selesai_trend_persen' => $trendSelesai,
                'total_ditolak' => $total_ditolak,
                'total_ditolak_trend_persen' => $trendDitolak,
                'rata_rata_sla_jam' => round($rata_rata_sla_menit / 60, 2),
                'rata_rata_sla_trend_persen' => $trendSla,
                'rata_rata_sla_text' => trim($sla_text),
            ];
        });
    }

    public function getChartTrend(DashboardFilter $filter): array
    {
        $requestParams = request()->all();
        $cacheKey = 'dashboard:chart_trend:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filter) {
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

            $data = $query->select(
                DB::raw('DATE(ajuan_create_datetime) as tanggal'),
                DB::raw('COUNT(ajuan_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as selesai")
            )
            ->groupBy(DB::raw('DATE(ajuan_create_datetime)'))
            ->orderBy('tanggal', 'asc')
            ->get();

            return $data->toArray();
        });
    }

    public function getTopWilayah(DashboardFilter $filter): array
    {
        $requestParams = request()->all();
        $cacheKey = 'dashboard:top_wilayah:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filter) {
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $data = $query->select(
                'ajuan_kecamatan_code as id_kecamatan',
                'ajuan_kecamatan_name as nama_kecamatan',
                DB::raw('COUNT(ajuan_id) as total')
            )
            ->whereNotNull('ajuan_kecamatan_code')
            ->groupBy('ajuan_kecamatan_code', 'ajuan_kecamatan_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

            return $data->toArray();
        });
    }
}