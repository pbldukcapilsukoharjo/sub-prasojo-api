<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class LembarKerjaFilter
{
    public function apply(
        Builder $query,
        array $filters
    ): Builder {

        $query->when(
            !empty($filters['search']),
            function (Builder $q) use ($filters) {

                $search = $filters['search'];

                $q->where(function (Builder $sub) use ($search) {

                    $sub->where(
                        'lk_ajuan_no_reg',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'lk_pelapor_role_name',
                        'like',
                        "%{$search}%"
                    );
                });
            }
        );

        $query->when(
            !empty($filters['district']),
            function (Builder $q) use ($filters) {

                $q->whereHas(
                    'ajuan',
                    fn (Builder $aq)
                        => $aq->where(
                            'ajuan_kecamatan_name',
                            $filters['district']
                        )
                );
            }
        );

        $query->when(
            !empty($filters['reporter']),
            function (Builder $q) use ($filters) {

                $q->where(
                    'lk_pelapor_role_name',
                    $filters['reporter']
                );
            }
        );

        $query->when(
            !empty($filters['start_date']),
            fn (Builder $q)
                => $q->whereDate(
                    'lk_create_datetime',
                    '>=',
                    $filters['start_date']
                )
        );

        $query->when(
            !empty($filters['end_date']),
            fn (Builder $q)
                => $q->whereDate(
                    'lk_create_datetime',
                    '<=',
                    $filters['end_date']
                )
        );

        return $query;
    }
}