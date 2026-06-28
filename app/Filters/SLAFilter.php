<?php

declare(strict_types=1);

namespace App\Services\Sla\Filters;

use App\Models\Ajuan;
use Illuminate\Database\Eloquent\Builder;

final class SlaFilter
{
    /**
     * @param Builder<Ajuan> $query
     * @param array<string,mixed> $filter
     *
     * @return Builder<Ajuan>
     */
    public function apply(
        Builder $query,
        array $filter,
    ): Builder {
        return $query
            ->when(
                $filter['start_date'] ?? null,
                fn (
                    Builder $q,
                    string $value,
                ) => $q->whereDate(
                    'ajuan_create_datetime',
                    '>=',
                    $value
                )
            )

            ->when(
                $filter['end_date'] ?? null,
                fn (
                    Builder $q,
                    string $value,
                ) => $q->whereDate(
                    'ajuan_create_datetime',
                    '<=',
                    $value
                )
            )

            ->when(
                $filter['layanan_kode'] ?? null,
                fn (
                    Builder $q,
                    string $value,
                ) => $q->where(
                    'ajuan_layanan_kode',
                    $value
                )
            )

            ->when(
                $filter['kecamatan_code'] ?? null,
                fn (
                    Builder $q,
                    string $value,
                ) => $q->where(
                    'ajuan_kecamatan_code',
                    $value
                )
            )

            ->when(
                $filter['operator_id'] ?? null,
                function (
                    Builder $q,
                    int $value,
                ): Builder {
                    return $q->whereHas(
                        'logStatuses',
                        fn (Builder $log) =>
                            $log->where(
                                'log_admin_id',
                                $value
                            )
                    );
                }
            );
    }
}