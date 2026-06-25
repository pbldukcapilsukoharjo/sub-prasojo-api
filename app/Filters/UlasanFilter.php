<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UlasanFilter
{
    public static function apply(
        Builder $query,
        array $filters
    ): Builder {

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where(
                    'review_content',
                    'like',
                    "%{$search}%"
                );

                $q->orWhereHas('ajuan', function ($ajuan) use ($search) {

                    $ajuan->where(
                        'ajuan_no_reg',
                        'like',
                        "%{$search}%"
                    );
                });
            });
        }

        if (
            !empty($filters['rating']) &&
            $filters['rating'] !== 'all'
        ) {
            $query->where(
                'review_rating',
                (int) $filters['rating']
            );
        }

        if (
            !empty($filters['serviceType']) &&
            $filters['serviceType'] !== 'all'
        ) {

            $serviceType = $filters['serviceType'];

            $query->whereHas('ajuan', function ($q) use ($serviceType) {

                $q->where(
                    'ajuan_layanan_kode',
                    $serviceType
                );
            });
        }

        if (
            !empty($filters['startDate']) &&
            !empty($filters['endDate'])
        ) {

            $query->whereBetween(
                'review_create_datetime',
                [
                    $filters['startDate'] . ' 00:00:00',
                    $filters['endDate'] . ' 23:59:59'
                ]
            );
        }

        match ($filters['sortBy'] ?? 'newest') {
            'oldest' => $query->orderBy(
                'review_create_datetime',
                'asc'
            ),

            default => $query->orderBy(
                'review_create_datetime',
                'desc'
            )
        };

        return $query;
    }
}