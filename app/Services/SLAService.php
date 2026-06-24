<?php

namespace App\Services;

use App\Models\Ajuan;
use App\Models\Layanan;
use Carbon\Carbon;

class SLAService
{
    public function getAll(array $filters): array
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = 5;

        $query = Ajuan::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where('ajuan_no_reg', 'like', "%{$search}%")
                    ->orWhere('ajuan_pelapor_nik', 'like', "%{$search}%")
                    ->orWhere('ajuan_kecamatan_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | District
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['district'])) {

            $query->where(
                'ajuan_kecamatan_name',
                $filters['district']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['startDate'])) {

            $query->whereDate(
                'ajuan_create_datetime',
                '>=',
                $filters['startDate']
            );
        }

        if (!empty($filters['endDate'])) {

            $query->whereDate(
                'ajuan_create_datetime',
                '<=',
                $filters['endDate']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['period'])) {

            switch ($filters['period']) {

                case 'today':
                    $query->whereDate(
                        'ajuan_create_datetime',
                        now()->toDateString()
                    );
                    break;

                case 'this_week':
                    $query->whereBetween(
                        'ajuan_create_datetime',
                        [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ]
                    );
                    break;

                case 'this_month':
                    $query->whereMonth(
                        'ajuan_create_datetime',
                        now()->month
                    )->whereYear(
                        'ajuan_create_datetime',
                        now()->year
                    );
                    break;

                case 'this_year':
                    $query->whereYear(
                        'ajuan_create_datetime',
                        now()->year
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Data
        |--------------------------------------------------------------------------
        */
        $allAjuan = (clone $query)->get();

        /*
        |--------------------------------------------------------------------------
        | Rata-rata waktu proses
        |--------------------------------------------------------------------------
        */
        $durations = $allAjuan->map(function ($ajuan) {

            if (
                empty($ajuan->ajuan_create_datetime) ||
                empty($ajuan->ajuan_update_datetime)
            ) {
                return 0;
            }

            return Carbon::parse($ajuan->ajuan_create_datetime)
                ->diffInMinutes(
                    Carbon::parse($ajuan->ajuan_update_datetime)
                ) / 60;
        });

        $avgDuration = round(
            (float) ($durations->avg() ?? 0),
            1
        );

        /*
        |--------------------------------------------------------------------------
        | Target SLA
        |--------------------------------------------------------------------------
        */
        $targetSla = 6;

        $achievedCount = $durations
            ->filter(fn($hour) => $hour <= $targetSla)
            ->count();

        $slaPercentage = $allAjuan->count() > 0
            ? round(
                ($achievedCount / $allAjuan->count()) * 100
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Detail Per Jenis Layanan
        |--------------------------------------------------------------------------
        */
        $list = [];

        $list[] = [
            'id' => 1,
            'jenis_layanan' => 'TOTAL AJUAN',
            'jumlah_ajuan' => $allAjuan->count(),
            'rata_rata_waktu' => $avgDuration
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

            $avg = $layananAjuan
                ->map(function ($ajuan) {

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

            $list[] = [
                'id' => $index + 2,
                'jenis_layanan' => strtoupper(
                    $layanan->layanan_nama
                ),
                'jumlah_ajuan' => $layananAjuan->count(),
                'rata_rata_waktu' => round(
                    (float) ($avg ?? 0),
                    1
                )
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        if (($filters['sortBy'] ?? 'newest') === 'oldest') {

            $list = collect($list)
                ->sortBy('rata_rata_waktu')
                ->values()
                ->toArray();
        } else {

            $list = collect($list)
                ->sortByDesc('rata_rata_waktu')
                ->values()
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination Manual
        |--------------------------------------------------------------------------
        */
        $total = count($list);

        $paginated = collect($list)
            ->forPage($page, $perPage)
            ->values()
            ->toArray();

        return [
            'rata_rata_waktu_proses' => $avgDuration,
            'pencapaian_sla' => $slaPercentage,
            'target_sla' => $targetSla,
            'daftar_rincian' => [
                'list' => $paginated,
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_page' => (int) ceil($total / $perPage),
                ]
            ]
        ];
    }
}