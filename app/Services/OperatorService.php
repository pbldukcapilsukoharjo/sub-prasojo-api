<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\OperatorFilter;
use App\Models\Admin;
use App\Models\Ajuan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class OperatorService
{
    /**
     * Get KPI for Operators.
     *
     * @param OperatorFilter $filter
     * @return array
     */
    public function getKpiGlobal(OperatorFilter $filter): array
    {
        $requestParams = request()->all();
        $cacheKey = 'operator:kpi_global:' . md5(json_encode($requestParams));

        return Cache::remember($cacheKey, 600, function () use ($filter) {
            $query = Admin::query()->where('level', Admin::LEVEL_OPERATOR);
            
            // Left join with ajuan to calculate performance metrics
            $query->leftJoin('ajuan', 'admin.id', '=', 'ajuan.ajuan_pelapor_id');
            
            $query = $filter->apply($query);
            $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

            $kpi = $query->select(
                DB::raw('COUNT(DISTINCT CASE WHEN admin.is_active = 1 THEN admin.id ELSE NULL END) as total_aktif'),
                DB::raw('COUNT(ajuan.ajuan_id) as total_berkas_dikerjakan'),
                DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
            )->first();

            $rataRataMenit = (float) ($kpi->rata_rata_waktu_menit ?? 0);
            
            $jam = floor($rataRataMenit / 60);
            $menit = round($rataRataMenit % 60);
            $text = "";
            if ($jam > 0) $text .= $jam . " Jam ";
            $text .= $menit . " Menit";
            if (trim($text) === "0 Menit") $text = "0 Menit";
            $text .= "/Berkas";

            return [
                'total_aktif' => (int) ($kpi->total_aktif ?? 0),
                'total_berkas_dikerjakan' => (int) ($kpi->total_berkas_dikerjakan ?? 0),
                'rata_rata_kecepatan_text' => trim($text),
            ];
        });
    }

    /**
     * Get Operator Ranking.
     *
     * @param OperatorFilter $filter
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRanking(OperatorFilter $filter, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->buildRankingQuery($filter);

        return $query->paginate($perPage);
    }

    /**
     * Get Query for Ranking and Export
     * 
     * @param OperatorFilter $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildRankingQuery(OperatorFilter $filter)
    {
        $query = Admin::query()->where('level', Admin::LEVEL_OPERATOR);
        $query->leftJoin('ajuan', 'admin.id', '=', 'ajuan.ajuan_pelapor_id');
        $query = $filter->apply($query);

        $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

        $query->select(
            'admin.id as id_operator',
            'admin.fullname as nama',
            DB::raw('COUNT(ajuan.ajuan_id) as total_berkas'),
            DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
        )
        ->groupBy('admin.id', 'admin.fullname')
        ->orderByRaw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) ASC");

        return $query;
    }

    /**
     * Get Operator Detail.
     *
     * @param int $idOperator
     * @return array
     */
    public function getDetail(int $idOperator): array
    {
        $admin = Admin::where('level', Admin::LEVEL_OPERATOR)->findOrFail($idOperator);
        $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

        $kpi = Ajuan::where('ajuan_pelapor_id', $idOperator)
            ->select(
                DB::raw('COUNT(ajuan_id) as total_dikerjakan'),
                DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
            )->first();

        $riwayat = Ajuan::where('ajuan_pelapor_id', $idOperator)
            ->whereIn('ajuan_status', AjuanStatus::getStatusSelesai())
            ->orderByDesc('ajuan_create_datetime')
            ->limit(50)
            ->get()
            ->map(function ($ajuan) {
                return [
                    'no_reg' => $ajuan->ajuan_no_reg,
                    'layanan' => $ajuan->ajuan_layanan_kode,
                    'waktu_mulai' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('H:i:s') : null,
                    'waktu_selesai' => $ajuan->ajuan_update_datetime ? $ajuan->ajuan_update_datetime->format('H:i:s') : null,
                    'durasi_menit' => $ajuan->ajuan_create_datetime && $ajuan->ajuan_update_datetime ? $ajuan->ajuan_create_datetime->diffInMinutes($ajuan->ajuan_update_datetime) : 0,
                ];
            });

        return [
            'profil' => [
                'nama' => $admin->fullname,
                'total_dikerjakan' => (int) ($kpi->total_dikerjakan ?? 0),
                'rata_rata_waktu_menit' => round((float) ($kpi->rata_rata_waktu_menit ?? 0), 2),
            ],
            'riwayat_kerja' => $riwayat->toArray()
        ];
    }
}
