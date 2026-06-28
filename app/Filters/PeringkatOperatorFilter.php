<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PeringkatOperatorFilter
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

                $builder
                    ->where(
                        'fullname',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'username',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Operator
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['operator'])) {

            $query->where(
                'id',
                $filters['operator']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kecamatan
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['district'])) {

            $query->where(
                'kecamatan_name',
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
                'create_datetime',
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
                        'create_datetime',
                        Carbon::today()
                    );

                    break;

                case 'this_week':

                    $query->whereBetween(
                        'create_datetime',
                        [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek(),
                        ]
                    );

                    break;

                case 'this_month':

                    $query->whereMonth(
                        'create_datetime',
                        Carbon::now()->month
                    )->whereYear(
                        'create_datetime',
                        Carbon::now()->year
                    );

                    break;

                case 'this_year':

                    $query->whereYear(
                        'create_datetime',
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

            'oldest' =>

                $query->orderBy(
                    'create_datetime',
                    'asc'
                ),

            default =>

                $query->orderBy(
                    'create_datetime',
                    'desc'
                ),
        };

        return $query;
    }
}