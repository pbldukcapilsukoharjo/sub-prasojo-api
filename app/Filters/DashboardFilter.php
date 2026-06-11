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

        return $query

            ->when(
                !empty($filters['startDate']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'create_datetime',
                        '>=',
                        $filters['startDate']
                    )
            )

            ->when(
                !empty($filters['endDate']),
                fn (Builder $query) =>
                    $query->whereDate(
                        'create_datetime',
                        '<=',
                        $filters['endDate']
                    )
            );
    }
}