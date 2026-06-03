<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class ProdukFilter
{
    public function apply(
        Builder $query,
        array $filters
    ): Builder {
        return $query

            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters): void {
                    $q->where(function (Builder $query) use ($filters): void {
                        $query
                            ->where('prod_ajuan_no_reg', 'like', "%{$filters['search']}%")
                            ->orWhere('prod_nama', 'like', "%{$filters['search']}%")
                            ->orWhere('prod_nomor', 'like', "%{$filters['search']}%");
                    });
                }
            )

            ->when(
                !empty($filters['district']),
                fn (Builder $q) =>
                    $q->whereHas(
                        'ajuan',
                        fn (Builder $ajuan) =>
                            $ajuan->where(
                                'ajuan_kecamatan_name',
                                $filters['district']
                            )
                    )
            )

            ->when(
                !empty($filters['status']),
                fn (Builder $q) =>
                    $q->where(
                        'prod_status',
                        $filters['status']
                    )
            )

            ->when(
                !empty($filters['startDate']),
                fn (Builder $q) =>
                    $q->whereDate(
                        'prod_create_datetime',
                        '>=',
                        $filters['startDate']
                    )
            )

            ->when(
                !empty($filters['endDate']),
                fn (Builder $q) =>
                    $q->whereDate(
                        'prod_create_datetime',
                        '<=',
                        $filters['endDate']
                    )
            );
    }
}