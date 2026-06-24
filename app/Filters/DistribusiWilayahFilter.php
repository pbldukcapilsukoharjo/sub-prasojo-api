<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class DistribusiWilayahFilter
{
    public function apply(
        Builder $query,
        array $filters
    ): Builder {

        if (!empty($filters['search'])) {

            $query->where(function ($q) use ($filters) {

                $q->where(
                    'ajuan_kelurahan_name',
                    'like',
                    '%' . $filters['search'] . '%'
                )
                ->orWhere(
                    'ajuan_kecamatan_name',
                    'like',
                    '%' . $filters['search'] . '%'
                );

            });
        }

        if (!empty($filters['district'])) {

            $query->where(
                'ajuan_kecamatan_name',
                $filters['district']
            );
        }

        if (
            !empty($filters['startDate'])
            &&
            !empty($filters['endDate'])
        ) {

            $query->whereBetween(
                'ajuan_create_datetime',
                [
                    $filters['startDate'],
                    $filters['endDate']
                ]
            );
        }

        return $query;
    }
}