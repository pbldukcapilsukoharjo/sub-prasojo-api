<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class DashboardFilter
{
    public function apply(
        Builder $query,
        array $filters
    ): Builder {

        if (
            !empty($filters['serviceType']) &&
            $filters['serviceType'] !== 'all'
        ) {
            $query->where(
                'ajuan_layanan_kode',
                $filters['serviceType']
            );
        }

        if (
            !empty($filters['district']) &&
            $filters['district'] !== 'all'
        ) {
            $query->where(
                'ajuan_kecamatan_name',
                $filters['district']
            );
        }

        if (
            !empty($filters['startDate'])
        ) {
            $query->whereDate(
                'ajuan_create_datetime',
                '>=',
                $filters['startDate']
            );
        }

        if (
            !empty($filters['endDate'])
        ) {
            $query->whereDate(
                'ajuan_create_datetime',
                '<=',
                $filters['endDate']
            );
        }

        return $query;
    }
}