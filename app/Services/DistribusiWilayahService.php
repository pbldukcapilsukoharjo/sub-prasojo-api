<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\DistribusiWilayahFilter;
use App\Models\Ajuan;

class DistribusiWilayahService
{
    /**
     * Mendapatkan data distribusi wilayah.
     */
    public function index(
        array $filters
    ): array {

        $page = (int) ($filters['page'] ?? 1);

        $perPage = (int) ($filters['per_page'] ?? 5);

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = Ajuan::query();

        DistribusiWilayahFilter::apply(
            $query,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Ambil seluruh data
        |--------------------------------------------------------------------------
        */

        $ajuans = (clone $query)->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan
        |--------------------------------------------------------------------------
        */

        $totalKecamatan = $ajuans
            ->pluck('ajuan_kecamatan_name')
            ->filter()
            ->unique()
            ->count();

        $totalAjuanDokumen = $ajuans->count();

        $rataRataAjuan = $totalKecamatan > 0
            ? round(
                $totalAjuanDokumen / $totalKecamatan
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Group berdasarkan Desa + Kecamatan
        |--------------------------------------------------------------------------
        */

        $grouped = $ajuans->groupBy(
            function (Ajuan $ajuan): string {

                return implode('|', [

                    $ajuan->ajuan_kelurahan_name,

                    $ajuan->ajuan_kecamatan_name,

                ]);
            }
        );

        $list = [];

        $id = 1;
        foreach ($grouped as $items) {

            /** @var Ajuan $first */
            $first = $items->first();

            $list[] = [

                'id' => $id++,

                'desa' =>
                    $first->ajuan_kelurahan_name,

                'kecamatan' =>
                    $first->ajuan_kecamatan_name,

                'total_ajuan' =>
                    $items->count(),

                'ktp-el' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'KTP'
                    )->count(),

                'kia' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'KIA'
                    )->count(),

                'akta_kelahiran' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'AKL'
                    )->count(),

                'akta_kematian' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'AKM'
                    )->count(),

                'perpindahan' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'PND'
                    )->count(),

                'kedatangan' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'DTG'
                    )->count(),

                'update_data' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'UPD'
                    )->count(),

                'rekam_jemput_bola' =>
                    $items->where(
                        'ajuan_layanan_kode',
                        'RKJ'
                    )->count(),

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Collection
        |--------------------------------------------------------------------------
        */

        $list = collect($list);

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match ($filters['sortBy'] ?? 'newest') {

            'oldest' =>

                $list = $list
                    ->sortBy('total_ajuan')
                    ->values(),

            default =>

                $list = $list
                    ->sortByDesc('total_ajuan')
                    ->values(),
        };

        /*
        |--------------------------------------------------------------------------
        | Manual Pagination
        |--------------------------------------------------------------------------
        */

        $total = $list->count();

        $paginated = $list
            ->forPage(
                $page,
                $perPage
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'total_kecamatan' =>
                $totalKecamatan,

            'total_ajuan_dokumen' =>
                $totalAjuanDokumen,

            'rata_rata_ajuan' =>
                $rataRataAjuan,

            'daftar_ajuan' => [

                'list' =>
                    $paginated,

                'meta' => [

                    'page' =>
                        $page,

                    'per_page' =>
                        $perPage,

                    'total' =>
                        $total,

                    'total_page' =>
                        (int) ceil(
                            $total / $perPage
                        ),

                ],

            ],

        ];
    }
}