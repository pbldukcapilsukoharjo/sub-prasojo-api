<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DistribusiWilayahFilter
{
    /**
     * Apply filter to query.
     */
    public static function apply(
        Builder $query,
        array $filters
    ): Builder {

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search) {

                $builder->where(
                    'ajuan_kecamatan_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'ajuan_kelurahan_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'ajuan_no_reg',
                    'like',
                    "%{$search}%"
                );
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
        if (
            !empty($filters['startDate']) &&
            !empty($filters['endDate'])
        ) {

            $query->whereBetween(
                'ajuan_create_datetime',
                [
                    $filters['startDate'] . ' 00:00:00',
                    $filters['endDate'] . ' 23:59:59',
                ]
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
                        Carbon::today()
                    );

                    break;

                case 'this_week':

                    $query->whereBetween(
                        'ajuan_create_datetime',
                        [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek(),
                        ]
                    );

                    break;

                case 'this_month':

                    $query->whereMonth(
                        'ajuan_create_datetime',
                        Carbon::now()->month
                    )->whereYear(
                        'ajuan_create_datetime',
                        Carbon::now()->year
                    );

                    break;

                case 'this_year':

                    $query->whereYear(
                        'ajuan_create_datetime',
                        Carbon::now()->year
                    );

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        match ($filters['sortBy'] ?? 'newest') {

            'oldest' => $query->orderBy(
                'ajuan_create_datetime',
                'asc'
            ),

            default => $query->orderBy(
                'ajuan_create_datetime',
                'desc'
            ),
        };

        return $query;
    }
}