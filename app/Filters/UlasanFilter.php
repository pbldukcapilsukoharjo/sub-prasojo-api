<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class UlasanFilter
{
    /**
     * Terapkan seluruh filter.
     *
     * @param array<string, mixed> $filters
     */
    public static function apply(
        Builder $query,
        array $filters
    ): Builder {
        self::search(
            $query,
            $filters['search'] ?? null
        );

        self::rating(
            $query,
            $filters['rating'] ?? null
        );

        self::serviceType(
            $query,
            $filters['serviceType'] ?? null
        );

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
     * Filter pencarian.
     */
    private static function search(
        Builder $query,
        ?string $keyword
    ): void {

        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($keyword): void {

            $builder
                ->where(
                    'review_content',
                    'like',
                    "%{$keyword}%"
                )

                ->orWhereHas(
                    'ajuan',
                    function (Builder $ajuan) use ($keyword): void {

                        $ajuan
                            ->where(
                                'ajuan_no_reg',
                                'like',
                                "%{$keyword}%"
                            )

                            ->orWhereHas(
                                'layanan',
                                function (Builder $layanan) use ($keyword): void {

                                    $layanan->where(
                                        'layanan_nama',
                                        'like',
                                        "%{$keyword}%"
                                    );
                                }
                            );
                    }
                );
        });
    }

    /**
     * Filter rating.
     */
    private static function rating(
        Builder $query,
        mixed $rating
    ): void {

        if (
            blank($rating) ||
            $rating === 'all'
        ) {
            return;
        }

        $query->where(
            'review_rating',
            (int) $rating
        );
    }

    /**
     * Filter layanan.
     */
    private static function serviceType(
        Builder $query,
        ?string $serviceType
    ): void {

        if (
            blank($serviceType) ||
            $serviceType === 'all'
        ) {
            return;
        }

        $query->whereHas(
            'ajuan',
            function (Builder $ajuan) use ($serviceType): void {

                $ajuan->where(
                    'ajuan_layanan_kode',
                    $serviceType
                );
            }
        );
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
            'review_create_datetime',
            [
                "{$startDate} 00:00:00",
                "{$endDate} 23:59:59",
            ]
        );
    }

    /**
     * Filter pengurutan.
     */
    private static function sort(
        Builder $query,
        string $sortBy
    ): void {

        $query->orderBy(
            'review_create_datetime',
            $sortBy === 'oldest'
                ? 'asc'
                : 'desc'
        );
    }
}