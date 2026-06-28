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

        // Polesan Amru: menentukan prefix kolom date secara eksplisit agar tidak ambiguous 
        // dengan join tabel lain, kita filter aktivitas berdasarkan waktu log status dibuat.
        $dateColumn = 'log_ajuan_status.log_create_datetime';

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                // Polesan Amru: berikan prefix tabel admin
                $builder
                    ->where(
                        'admin.fullname',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'admin.username',
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
                'admin.id',
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
                'admin.kecamatan_name',
                $filters['district']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween(
                $dateColumn,
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
                    $query->whereDate($dateColumn, Carbon::today());
                    break;
                case 'this_week':
                    $query->whereBetween($dateColumn, [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth($dateColumn, Carbon::now()->month)
                          ->whereYear($dateColumn, Carbon::now()->year);
                    break;
                case 'this_year':
                    $query->whereYear($dateColumn, Carbon::now()->year);
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        // Falah melakuan sort secara collection di service, jadi ini sort default database
        // Polesan Amru: sort default tidak diaplikasikan di sini karena di service ranking diurutkan dari aggregate
        
        return $query;
    }
}