<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UlasanFilter
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

            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {

                $builder->where(
                    'review_content',
                    'LIKE',
                    "%{$search}%"
                );

                $builder->orWhereHas(
                    'ajuan',
                    function (Builder $ajuan) use ($search) {

                        $ajuan->where(
                            'ajuan_no_reg',
                            'LIKE',
                            "%{$search}%"
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Rating
        |--------------------------------------------------------------------------
        */
        if (
            isset($filters['rating']) &&
            $filters['rating'] !== 'all'
        ) {

            $query->where(
                'review_rating',
                (int) $filters['rating']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Service Type
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['serviceType'])) {

            $query->whereHas(
                'ajuan',
                function (Builder $ajuan) use ($filters) {

                    $ajuan->where(
                        'ajuan_layanan_kode',
                        $filters['serviceType']
                    );
                }
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
                'review_create_datetime',
                [
                    $filters['startDate'] . ' 00:00:00',
                    $filters['endDate'] . ' 23:59:59',
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        switch ($filters['sortBy'] ?? 'newest') {

            case 'oldest':

                $query->orderBy(
                    'review_create_datetime',
                    'asc'
                );

                break;

            case 'newest':

            default:

                $query->orderByDesc(
                    'review_create_datetime'
                );

                break;
        }

        return $query;
    }
}