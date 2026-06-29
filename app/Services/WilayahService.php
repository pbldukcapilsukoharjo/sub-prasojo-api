<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\WilayahFilter;
use App\Models\Ajuan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WilayahService
{
    /**
     * Get Distribusi Wilayah with Pagination.
     *
     * @param WilayahFilter $filter
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDistribusi(WilayahFilter $filter, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->buildDistribusiQuery($filter);

        $paginator = $query->paginate($perPage);
        $paginator->getCollection()->transform(function ($item) {
            return $this->formatDistribusiItem($item);
        });

        return $paginator;
    }

    /**
     * Get Query Builder for Distribusi (used for Export as well).
     *
     * @param WilayahFilter $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildDistribusiQuery(WilayahFilter $filter)
    {
        $query = Ajuan::query();
        $query = $filter->apply($query);

        $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

        // Cek apakah filter id_kecamatan diterapkan
        $isFilteredByKecamatan = request()->has('id_kecamatan') && request()->get('id_kecamatan') != '';

        if ($isFilteredByKecamatan) {
            $query->whereNotNull('ajuan_desa_code')
                  ->where('ajuan_desa_code', '!=', '');

            $query->select(
                'ajuan_desa_code as id_wilayah',
                'ajuan_desa_name as nama_wilayah',
                DB::raw('COUNT(ajuan_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
            )
            ->groupBy('ajuan_desa_code', 'ajuan_desa_name')
            ->orderBy('ajuan_desa_name', 'asc');
        } else {
            // Pastikan tidak ada record tanpa kode kecamatan yang masuk
            $query->whereNotNull('ajuan_kecamatan_code')
                  ->where('ajuan_kecamatan_code', '!=', '');

            $query->select(
                'ajuan_kecamatan_code as id_wilayah',
                'ajuan_kecamatan_name as nama_wilayah',
                DB::raw('COUNT(ajuan_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                DB::raw("AVG(CASE WHEN ajuan_status IN ($statusSelesai) THEN TIMESTAMPDIFF(MINUTE, ajuan_create_datetime, ajuan_update_datetime) ELSE NULL END) as rata_rata_waktu_menit")
            )
            ->groupBy('ajuan_kecamatan_code', 'ajuan_kecamatan_name')
            ->orderBy('ajuan_kecamatan_name', 'asc');
        }

        return $query;
    }

    /**
     * Format individual item to expected API response structure.
     *
     * @param object $item
     * @return array
     */
    public function formatDistribusiItem(object $item): array
    {
        $totalAjuan = (int) $item->total_ajuan;
        $totalSelesai = (int) $item->total_selesai;
        
        $rasioSelesai = $totalAjuan > 0 ? ($totalSelesai / $totalAjuan) * 100 : 0;
        
        $rataRataMenit = (float) ($item->rata_rata_waktu_menit ?? 0);
        $jam = floor($rataRataMenit / 60);
        $menit = round($rataRataMenit % 60);
        
        $textWaktu = "";
        if ($jam > 0) $textWaktu .= $jam . " Jam ";
        $textWaktu .= $menit . " Menit";
        if (trim($textWaktu) === "0 Menit") $textWaktu = "0 Menit";

        $isFilteredByKecamatan = request()->has('id_kecamatan') && request()->get('id_kecamatan') != '';

        $response = [
            'total_ajuan' => $totalAjuan,
            'rata_rata_waktu' => trim($textWaktu),
            'rasio_selesai_persen' => round($rasioSelesai, 2),
        ];

        if ($isFilteredByKecamatan) {
            $response['id_desa'] = $item->id_wilayah;
            $response['nama_desa'] = $item->nama_wilayah;
        } else {
            $response['id_kecamatan'] = $item->id_wilayah;
            $response['nama_kecamatan'] = $item->nama_wilayah;
        }

        return $response;
    }
}
