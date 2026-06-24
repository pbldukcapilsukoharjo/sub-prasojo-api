<?php

namespace App\Services;

use App\Models\Ajuan;
use App\Filters\DistribusiWilayahFilter;

class DistribusiWilayahService
{
    public function getAll(array $filters): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = 5;

        $query = Ajuan::query();

        $query = (new DistribusiWilayahFilter())
            ->apply($query, $filters);

        $totalKecamatan = (clone $query)
            ->distinct()
            ->count('ajuan_kecamatan_name');

        $totalAjuanDokumen = (clone $query)->count();

        $grouped = (clone $query)
            ->selectRaw("
                MIN(ajuan_id) as id,

                ajuan_kelurahan_name as desa,
                ajuan_kecamatan_name as kecamatan,

                COUNT(*) as total_ajuan,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'KTP'
                        THEN 1
                        ELSE 0
                    END
                ) as ktp_el,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'KIA'
                        THEN 1
                        ELSE 0
                    END
                ) as kia,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'AKL'
                        THEN 1
                        ELSE 0
                    END
                ) as akta_kelahiran,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'AKM'
                        THEN 1
                        ELSE 0
                    END
                ) as akta_kematian,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'PND'
                        THEN 1
                        ELSE 0
                    END
                ) as perpindahan,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'DTG'
                        THEN 1
                        ELSE 0
                    END
                ) as kedatangan,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'UPD'
                        THEN 1
                        ELSE 0
                    END
                ) as update_data,

                SUM(
                    CASE
                        WHEN ajuan_layanan_kode = 'RKJ'
                        THEN 1
                        ELSE 0
                    END
                ) as rekam_jemput_bola
            ")
            ->groupBy(
                'ajuan_kelurahan_name',
                'ajuan_kecamatan_name'
            );

        $totalData = (clone $grouped)
            ->get()
            ->count();

        $list = $grouped
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($item) {

                return [

                    'id' => (int) $item->id,

                    'desa' => $item->desa,

                    'kecamatan' => $item->kecamatan,

                    'total_ajuan' =>
                        (int) $item->total_ajuan,

                    'ktp_el' =>
                        (int) $item->ktp_el,

                    'kia' =>
                        (int) $item->kia,

                    'akta_kelahiran' =>
                        (int) $item->akta_kelahiran,

                    'akta_kematian' =>
                        (int) $item->akta_kematian,

                    'perpindahan' =>
                        (int) $item->perpindahan,

                    'kedatangan' =>
                        (int) $item->kedatangan,

                    'update_data' =>
                        (int) $item->update_data,

                    'rekam_jemput_bola' =>
                        (int) $item->rekam_jemput_bola,
                ];
            })
            ->values();

        return [

            'total_kecamatan' =>
                (int) $totalKecamatan,

            'total_ajuan_dokumen' =>
                (int) $totalAjuanDokumen,

            'rata_rata_ajuan' =>
                $totalKecamatan > 0
                    ? (int) round(
                        $totalAjuanDokumen / $totalKecamatan
                    )
                    : 0,

            'list' => $list,

            'meta' => [

                'page' =>
                    $page,

                'per_page' =>
                    $perPage,

                'total' =>
                    (int) $totalData,

                'total_page' =>
                    (int) ceil(
                        $totalData / $perPage
                    ),
            ],
        ];
    }
}