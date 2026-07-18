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

            return $paginator;
        } catch (\Throwable $e) {
            Log::error('[WilayahService@getDistribusi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get Matrix Tabel Distribusi (Baris: Wilayah, Kolom: Layanan)
     *
     * @param WilayahFilter $filter
     * @return array
     */
    public function getMatriks(WilayahFilter $filter): array
    {
        try {
            $query = Ajuan::query();
            $query = $filter->apply($query);

            $layanans = Layanan::orderBy('layanan_pos')->get();

            $isFilteredByKecamatan = request()->has('id_kecamatan') && request()->get('id_kecamatan') != '';

            if ($isFilteredByKecamatan) {
                $query->whereNotNull('ajuan_kelurahan_code')->where('ajuan_kelurahan_code', '!=', '');
                $groupBy = ['ajuan_kelurahan_code', 'ajuan_kelurahan_name'];
                $select = ['ajuan_kelurahan_code as id_wilayah', 'ajuan_kelurahan_name as nama_wilayah', 'ajuan_layanan_kode'];
            } else {
                $query->whereNotNull('ajuan_kecamatan_code')->where('ajuan_kecamatan_code', '!=', '');
                $groupBy = ['ajuan_kecamatan_code', 'ajuan_kecamatan_name'];
                $select = ['ajuan_kecamatan_code as id_wilayah', 'ajuan_kecamatan_name as nama_wilayah', 'ajuan_layanan_kode'];
            }

            $query->select(array_merge($select, [
                DB::raw('COUNT(ajuan_id) as total_ajuan')
            ]))
            ->groupBy(array_merge($groupBy, ['ajuan_layanan_kode']));

            $rawData = $query->get();

            $matrix = [];
            foreach ($rawData as $row) {
                $wilayahId = $row->id_wilayah;
                if (!isset($matrix[$wilayahId])) {
                    $matrix[$wilayahId] = [
                        'id_wilayah' => $row->id_wilayah,
                        'nama_wilayah' => $row->nama_wilayah,
                        'layanan' => []
                    ];
                    // inisiasi 0
                    foreach ($layanans as $l) {
                        $matrix[$wilayahId]['layanan'][$l->layanan_kode] = 0;
                    }
                }
                $matrix[$wilayahId]['layanan'][$row->ajuan_layanan_kode] = $row->total_ajuan;
            }

            return array_values($matrix);
        } catch (\Throwable $e) {
            Log::error('[WilayahService@getMatriks] ' . $e->getMessage(), [
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
            $isFilteredByKecamatan = request()->has('id_kecamatan') && request()->get('id_kecamatan') != '';

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
