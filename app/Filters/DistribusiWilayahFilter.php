<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class DistribusiWilayahFilter
{
    /**
     * Terapkan seluruh filter.
     */
    public static function apply(
        Builder $query,
        array $filters
    ): Builder {

        self::search($query, $filters['search'] ?? null);

        self::district($query, $filters['district'] ?? null);

        self::period($query, $filters['period'] ?? null);

        self::dateRange(
            $query,
            $filters['startDate'] ?? null,
            $filters['endDate'] ?? null
        );

        self::sort(
            $query,
            $filters['sortBy'] ?? 'newest'
        );

        return $query;
    }

    /**
     * Cari berdasarkan nama desa atau kecamatan.
     */
    private static function search(
        Builder $query,
        ?string $keyword
    ): void {

        if (blank($keyword)) {
            return;
        }

        $query->where(function (Builder $builder) use ($keyword): void {

            $builder
                ->where(
                    'ajuan_kelurahan_name',
                    'like',
                    "%{$keyword}%"
                )
                ->orWhere(
                    'ajuan_kecamatan_name',
                    'like',
                    "%{$keyword}%"
                );
        });
    }

    /**
     * Filter kecamatan.
     */
    private static function district(
        Builder $query,
        ?string $district
    ): void {

        if (blank($district) || $district === 'all') {
            return;
        }

        $query->where(
            'ajuan_kecamatan_name',
            $district
        );
    }

    /**
     * Filter periode.
     */
    private static function period(
        Builder $query,
        ?string $period
    ): void {

        if (
            blank($period) ||
            $period === 'all'
        ) {
            return;
        }

        match ($period) {

            'today' => $query->whereDate(
                'ajuan_create_datetime',
                Carbon::today()
            ),

            'week' => $query->whereBetween(
                'ajuan_create_datetime',
                [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]
            ),

            'month' => $query->whereMonth(
                'ajuan_create_datetime',
                Carbon::now()->month
            )->whereYear(
                'ajuan_create_datetime',
                Carbon::now()->year
            ),

            'year' => $query->whereYear(
                'ajuan_create_datetime',
                Carbon::now()->year
            ),

            default => null,
        };
    }

    /**
     * Filter rentang tanggal.
     */
    private static function dateRange(
        Builder $query,
        ?string $startDate,
        ?string $endDate
    ): void {

        if (
            blank($startDate) ||
            blank($endDate)
        ) {
            return;
        }

        $query->whereBetween(
            'ajuan_create_datetime',
            [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]
        );
    }

    /**
     * Sorting.
     *
     * Catatan:
     * most_submission & least_submission akan dipakai
     * setelah query di-group oleh service.
     */
    private static function sort(
        Builder $query,
        string $sortBy
    ): void {

        match ($sortBy) {

            'oldest' => $query->orderBy(
                'ajuan_create_datetime'
            ),

            'most_submission' => $query->orderByDesc(
                'total_ajuan'
            ),

            'least_submission' => $query->orderBy(
                'total_ajuan'
            ),

            default => $query->orderByDesc(
                'ajuan_create_datetime'
            ),
        };
    }
}