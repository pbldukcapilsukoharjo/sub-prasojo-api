<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjuanStatus;
use App\Filters\WilayahFilter;
use App\Models\Prasojo\Ajuan;
use App\Models\Prasojo\Layanan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            $query = $this->buildDistribusiQuery($filter);

            $paginator = $query->paginate($perPage);
            $paginator->getCollection()->transform(function ($item) {
                return $this->formatDistribusiItem($item);
            });

            $items = $paginator->items();
            if (!empty($items)) {
                $isFilteredByKecamatan = !empty($filter->request['id_kecamatan']) || (request()->has('id_kecamatan') && request()->input('id_kecamatan') != '');
                $wilayahCol = $isFilteredByKecamatan ? 'ajuan_kelurahan_code' : 'ajuan_kecamatan_code';
                $wilayahKey = $isFilteredByKecamatan ? 'id_desa' : 'id_kecamatan';

                $wilayahIds = array_map(function($item) use ($wilayahKey) {
                    return $item[$wilayahKey];
                }, $items);

                $layananQuery = Ajuan::query();
                $layananQuery = $filter->apply($layananQuery);
                
                $layananCounts = $layananQuery->whereIn("ajuan.{$wilayahCol}", $wilayahIds)
                    ->select("ajuan.{$wilayahCol}", 'ajuan.ajuan_layanan_kode', DB::raw('COUNT(ajuan.ajuan_id) as total'))
                    ->groupBy("ajuan.{$wilayahCol}", 'ajuan.ajuan_layanan_kode')
                    ->get();

                $layananMap = [];
                foreach ($layananCounts as $row) {
                    $wId = $row->{$wilayahCol};
                    if (!isset($layananMap[$wId])) {
                        $layananMap[$wId] = [];
                    }
                    $layananMap[$wId][$row->ajuan_layanan_kode] = $row->total;
                }

                $layanans = Layanan::orderBy('layanan_pos')->pluck('layanan_kode')->toArray();

                $paginator->getCollection()->transform(function ($item) use ($layananMap, $layanans, $wilayahKey) {
                    $wId = $item[$wilayahKey];
                    $item['layanan'] = [];
                    foreach ($layanans as $layananKode) {
                        $item['layanan'][$layananKode] = $layananMap[$wId][$layananKode] ?? 0;
                    }
                    return $item;
                });
            }

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[WilayahService@getDistribusi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get KPI Wilayah
     *
     * @param WilayahFilter $filter
     * @return array
     */
    public function getKpi(WilayahFilter $filter): array
    {
        try {
            $query = Ajuan::query();
            $query = $filter->apply($query);
            $totalAjuan = $query->count('ajuan_id');

            $isFilteredByKecamatan = !empty($filter->request['id_kecamatan']) || (request()->has('id_kecamatan') && request()->input('id_kecamatan') != '');

            $wilayahQuery = Ajuan::query();
            $wilayahQuery = $filter->apply($wilayahQuery);

            if ($isFilteredByKecamatan) {
                $jumlahWilayah = $wilayahQuery->whereNotNull('ajuan_kelurahan_code')
                                              ->where('ajuan_kelurahan_code', '!=', '')
                                              ->distinct('ajuan_kelurahan_code')
                                              ->count('ajuan_kelurahan_code');
                $labelWilayah = 'jumlah_desa';
            } else {
                $jumlahWilayah = $wilayahQuery->whereNotNull('ajuan_kecamatan_code')
                                              ->where('ajuan_kecamatan_code', '!=', '')
                                              ->distinct('ajuan_kecamatan_code')
                                              ->count('ajuan_kecamatan_code');
                $labelWilayah = 'jumlah_kecamatan';
            }

            $rataRata = $jumlahWilayah > 0 ? $totalAjuan / $jumlahWilayah : 0;

            return [
                $labelWilayah => $jumlahWilayah,
                'total_ajuan' => $totalAjuan,
                'rata_rata_ajuan' => (int) round($rataRata)
            ];
        } catch (\Throwable $e) {
            Log::error('[WilayahService@getKpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get Query Builder for Distribusi (used for Export as well).
     *
     * @param WilayahFilter $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildDistribusiQuery(WilayahFilter $filter)
    {
        try {
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $statusSelesai = "'" . implode("','", AjuanStatus::getStatusSelesai()) . "'";

            $defaultDb = config('database.connections.mysql.database');
            $query->leftJoin(DB::raw("`{$defaultDb}`.`ajuan_sla_summaries` as sla_summary"), 'sla_summary.ajuan_id', '=', 'ajuan.ajuan_id');

            // Cek apakah filter id_kecamatan diterapkan
            $isFilteredByKecamatan = !empty($filter->request['id_kecamatan']) || (request()->has('id_kecamatan') && request()->input('id_kecamatan') != '');

            if ($isFilteredByKecamatan) {
                $query->whereNotNull('ajuan.ajuan_kelurahan_code')
                      ->where('ajuan.ajuan_kelurahan_code', '!=', '');

                $query->select(
                    'ajuan.ajuan_kelurahan_code as id_wilayah',
                    'ajuan.ajuan_kelurahan_name as nama_wilayah',
                    DB::raw('COUNT(ajuan.ajuan_id) as total_ajuan'),
                    DB::raw("SUM(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                    DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN sla_summary.durasi_sla_menit ELSE NULL END) as rata_rata_waktu_menit")
                )
                ->groupBy('ajuan.ajuan_kelurahan_code', 'ajuan.ajuan_kelurahan_name')
                ->orderBy('ajuan.ajuan_kelurahan_name', 'asc');
            } else {
                // Pastikan tidak ada record tanpa kode kecamatan yang masuk
                $query->whereNotNull('ajuan.ajuan_kecamatan_code')
                      ->where('ajuan.ajuan_kecamatan_code', '!=', '');

                $query->select(
                    'ajuan.ajuan_kecamatan_code as id_wilayah',
                    'ajuan.ajuan_kecamatan_name as nama_wilayah',
                    DB::raw('COUNT(ajuan.ajuan_id) as total_ajuan'),
                    DB::raw("SUM(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN 1 ELSE 0 END) as total_selesai"),
                    DB::raw("AVG(CASE WHEN ajuan.ajuan_status IN ($statusSelesai) THEN sla_summary.durasi_sla_menit ELSE NULL END) as rata_rata_waktu_menit")
                )
                ->groupBy('ajuan.ajuan_kecamatan_code', 'ajuan.ajuan_kecamatan_name')
                ->orderBy('ajuan.ajuan_kecamatan_name', 'asc');
            }

            return $query;
        } catch (\Throwable $e) {
            Log::error('[WilayahService@buildDistribusiQuery] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

        $isFilteredByKecamatan = request()->has('id_kecamatan') && request()->input('id_kecamatan') != '';

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
