<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PeringkatOperatorFilter
{
    public static function apply(
        Builder $query,
        array $filters
    ): Builder {

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('fullname', 'like', "%{$search}%");

            });

        }

        if (!empty($filters['district'])) {

            $query->where(
                'kecamatan_name',
                $filters['district']
            );

        }

        if (!empty($filters['operator'])) {

            $query->where(
                'fullname',
                $filters['operator']
            );

        }

        if (!empty($filters['startDate'])) {

            $query->whereDate(
                'create_datetime',
                '>=',
                $filters['startDate']
            );

        }

        if (!empty($filters['endDate'])) {

            $query->whereDate(
                'create_datetime',
                '<=',
                $filters['endDate']
            );

        }

        switch ($filters['sortBy'] ?? 'newest') {

            case 'oldest':
                $query->orderBy(
                    'create_datetime',
                    'asc'
                );
                break;

            case 'highest':
                $query->orderBy(
                    'total_ajuan',
                    'desc'
                );
                break;

            case 'lowest':
                $query->orderBy(
                    'total_ajuan',
                    'asc'
                );
                break;

            default:
                $query->orderBy(
                    'create_datetime',
                    'desc'
                );

        }

        return $query;
    }
}