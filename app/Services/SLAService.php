<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\SLAFilter;
use App\Models\Ajuan;
use App\Models\Layanan;
use Carbon\Carbon;

class SLAService
{
    /**
     * Mendapatkan data SLA.
     */
    public function index(array $filters): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 5);

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = Ajuan::query()
            ->with('layanan');

        SLAFilter::apply(
            $query,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Ambil seluruh data sesuai filter
        |--------------------------------------------------------------------------
        */

        $ajuanCollection = (clone $query)->get();

        /*
        |--------------------------------------------------------------------------
        | Hitung rata-rata waktu proses
        |--------------------------------------------------------------------------
        */

        $durations = $ajuanCollection->map(
            function (Ajuan $ajuan): float {

                if (
                    empty($ajuan->ajuan_create_datetime) ||
                    empty($ajuan->ajuan_update_datetime)
                ) {
                    return 0;
                }

                return Carbon::parse(
                    $ajuan->ajuan_create_datetime
                )->diffInMinutes(
                    Carbon::parse(
                        $ajuan->ajuan_update_datetime
                    )
                ) / 60;
            }
        );

        $averageProcessTime = round(
            (float) ($durations->avg() ?? 0),
            1
        );

        /*
        |--------------------------------------------------------------------------
        | Target SLA
        |--------------------------------------------------------------------------
        */

        $targetSla = 6;

        /*
        |--------------------------------------------------------------------------
        | Pencapaian SLA
        |--------------------------------------------------------------------------
        */

        $achievedCount = $durations
            ->filter(
                fn (float $hour): bool => $hour <= $targetSla
            )
            ->count();

        $slaAchievement = $ajuanCollection->count() > 0
            ? round(
                ($achievedCount / $ajuanCollection->count()) * 100
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Detail per layanan
        |--------------------------------------------------------------------------
        */

        $details = [];

        $details[] = [
            'id' => 1,
            'jenis_layanan' => 'TOTAL AJUAN',
            'jumlah_ajuan' => $ajuanCollection->count(),
            'rata_rata_waktu' => $averageProcessTime,
        ];

        $layanans = Layanan::query()
            ->orderBy('layanan_pos')
            ->get();
            
foreach ($layanans as $index => $layanan) {

            $layananAjuan = (clone $query)
                ->where(
                    'ajuan_layanan_kode',
                    $layanan->layanan_kode
                )
                ->get();

            $average = $layananAjuan
                ->map(function (Ajuan $ajuan): float {

                    if (
                        empty($ajuan->ajuan_create_datetime) ||
                        empty($ajuan->ajuan_update_datetime)
                    ) {
                        return 0;
                    }

                    return Carbon::parse(
                        $ajuan->ajuan_create_datetime
                    )->diffInMinutes(
                        Carbon::parse(
                            $ajuan->ajuan_update_datetime
                        )
                    ) / 60;
                })
                ->avg();

            $details[] = [
                'id' => $index + 2,
                'jenis_layanan' => strtoupper(
                    $layanan->layanan_nama
                ),
                'jumlah_ajuan' => $layananAjuan->count(),
                'rata_rata_waktu' => round(
                    (float) ($average ?? 0),
                    1
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $details = collect($details);

        match ($filters['sortBy'] ?? 'newest') {

            'oldest' => $details = $details
                ->sortBy('rata_rata_waktu')
                ->values(),

            default => $details = $details
                ->sortByDesc('rata_rata_waktu')
                ->values(),
        };

        /*
        |--------------------------------------------------------------------------
        | Manual Pagination
        |--------------------------------------------------------------------------
        */

        $total = $details->count();

        $list = $details
            ->forPage($page, $perPage)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'rata_rata_waktu_proses' =>
                $averageProcessTime,

            'pencapaian_sla' =>
                $slaAchievement,

            'target_sla' =>
                $targetSla,

            'daftar_rincian' => [

                'list' =>
                    $list,

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