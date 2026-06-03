<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class AjuanFilter
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
                            ->where('ajuan_no_reg', 'like', "%{$filters['search']}%")
                            ->orWhere('ajuan_layanan_kode', 'like', "%{$filters['search']}%")
                            ->orWhere('ajuan_kecamatan_name', 'like', "%{$filters['search']}%");
                    });
                }
            )

            ->when(
                !empty($filters['district']),
                fn (Builder $q) =>
                    $q->where(
                        'ajuan_kecamatan_name',
                        $filters['district']
                    )
            )

            ->when(
                !empty($filters['status']),
                fn (Builder $q) =>
                    $q->where(
                        'ajuan_status',
                        $filters['status']
                    )
            )

            ->when(
                !empty($filters['reporter']),
                fn (Builder $q) =>
                    $q->where(
                        'ajuan_pelapor_role_name',
                        $filters['reporter']
                    )
            )

            ->when(
                !empty($filters['startDate']),
                fn (Builder $q) =>
                    $q->whereDate(
                        'ajuan_create_datetime',
                        '>=',
                        $filters['startDate']
                    )
            )

            ->when(
                !empty($filters['endDate']),
                fn (Builder $q) =>
                    $q->whereDate(
                        'ajuan_create_datetime',
                        '<=',
                        $filters['endDate']
                    )
            );
    }
}
