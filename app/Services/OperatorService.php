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
                DB::raw('COUNT(ajuan.ajuan_id) as total_ajuan'),
                DB::raw("COUNT(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN 1 ELSE NULL END) as total_selesai"),
                DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
            )->first();

            $totalAjuan = (int) ($kpi->total_ajuan ?? 0);
            $totalSelesai = (int) ($kpi->total_selesai ?? 0);
            $rataRataMenit = (float) ($kpi->rata_rata_waktu_menit ?? 0);
            
            $tingkatSelesai = $totalAjuan > 0 ? round(($totalSelesai / $totalAjuan) * 100, 1) : 0;
            $rataRataDurasi = round($rataRataMenit, 1);

            return [
                'total_ajuan' => $totalAjuan,
                'total_selesai' => $totalSelesai,
                'tingkat_selesai' => $tingkatSelesai,
                'rata_rata_durasi' => $rataRataDurasi,
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
            'admin.kelurahan_name as desa',
            'admin.kecamatan_name as kecamatan_nama',
            DB::raw('COUNT(ajuan.ajuan_id) as total_berkas'),
            DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
        )
        ->groupBy('admin.id', 'admin.fullname', 'admin.kelurahan_name', 'admin.kecamatan_name')
        ->orderByRaw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, ajuan.ajuan_update_datetime) ELSE NULL END) ASC");

        return $query;
    }

    /**
     * Get KPI for a specific operator.
     *
     * @param int $idOperator
     * @param array $requestParams
     * @return array
     */
    public function getDetailKpi(int $idOperator, array $requestParams): array
    {
        $admin = Admin::where('level', Admin::LEVEL_OPERATOR)->findOrFail($idOperator);
        $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

        $query = Ajuan::where('ajuan_pelapor_id', $idOperator);

        if (!empty($requestParams['tahun'])) {
            $query->whereYear('ajuan_create_datetime', $requestParams['tahun']);
        }
        if (!empty($requestParams['periode_bulan'])) {
            $query->whereMonth('ajuan_create_datetime', $requestParams['periode_bulan']);
        }
        if (!empty($requestParams['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $requestParams['id_layanan']);
        }

        $kpi = (clone $query)->select(
            DB::raw('COUNT(ajuan_id) as total_ajuan'),
            DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai")
        )->first();

        $totalAjuan = (int) ($kpi->total_ajuan ?? 0);
        $totalSelesai = (int) ($kpi->total_selesai ?? 0);
        $tingkatSelesai = $totalAjuan > 0 ? (int) round(($totalSelesai / $totalAjuan) * 100) : 0;

        $chartData = [
            'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'Mei' => 0, 'Jun' => 0,
            'Jul' => 0, 'Agu' => 0, 'Sep' => 0, 'Okt' => 0, 'Nov' => 0, 'Des' => 0
        ];
        
        $monthsMap = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $chartQuery = (clone $query)->select(
            DB::raw('MONTH(ajuan_create_datetime) as bulan'),
            DB::raw('COUNT(ajuan_id) as total')
        )->groupBy('bulan')->get();

        foreach ($chartQuery as $item) {
            if ($item->bulan >= 1 && $item->bulan <= 12) {
                $chartData[$monthsMap[$item->bulan - 1]] = (int) $item->total;
            }
        }

        return [
            'id' => $admin->id,
            'nama' => $admin->fullname,
            'total_ajuan' => $totalAjuan,
            'total_selesai' => $totalSelesai,
            'tingkat_selesai' => $tingkatSelesai,
            'layanan_perbulan' => $chartData
        ];
    }

    /**
     * Get Riwayat for a specific operator.
     *
     * @param int $idOperator
     * @param array $requestParams
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDetailRiwayat(int $idOperator, array $requestParams, int $perPage = 10): LengthAwarePaginator
    {
        Admin::where('level', Admin::LEVEL_OPERATOR)->findOrFail($idOperator);
        
        $query = Ajuan::where('ajuan_pelapor_id', $idOperator)->with('pelapor');

        if (!empty($requestParams['tahun'])) {
            $query->whereYear('ajuan_create_datetime', $requestParams['tahun']);
        }
        if (!empty($requestParams['periode_bulan'])) {
            $query->whereMonth('ajuan_create_datetime', $requestParams['periode_bulan']);
        }
        if (!empty($requestParams['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $requestParams['id_layanan']);
        }
        if (!empty($requestParams['search'])) {
            $search = $requestParams['search'];
            $query->where('ajuan_no_reg', 'like', "%{$search}%");
        }

        $query->orderByDesc('ajuan_create_datetime');

        $paginator = $query->paginate($perPage);
        
        $mappedData = collect($paginator->items())->map(function ($ajuan) {
            return [
                'id' => $ajuan->ajuan_id,
                'no_regis' => $ajuan->ajuan_no_reg,
                'pemohon' => $ajuan->pelapor ? $ajuan->pelapor->fullname : '-',
                'kode_ajuan' => $ajuan->ajuan_layanan_kode,
                'desa' => $ajuan->ajuan_kelurahan_name ?? '-',
                'tanggal' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('d-m-Y') : '-',
                'waktu' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('H:i') : '-',
                'status' => $ajuan->ajuan_status,
            ];
        })->toArray();

        $paginator->setCollection(collect($mappedData));

        return $paginator;
    }
}
