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
        return $query

            ->when(
                !empty($filters['status']),
                fn (Builder $q) =>
                    $q->where(
                        'lk_status',
                        $filters['status']
                    )
            )

            ->when(
                !empty($filters['layanan_kode']),
                fn (Builder $q) =>
                    $q->where(
                        'lk_layanan_kode',
                        $filters['layanan_kode']
                    )
            )

            ->when(
                isset($filters['is_online']),
                fn (Builder $q) =>
                    $q->where(
                        'lk_ajuan_is_online',
                        $filters['is_online']
                    )
            )

            ->when(
                isset($filters['is_mandiri']),
                fn (Builder $q) =>
                    $q->where(
                        'lk_ajuan_is_mandiri',
                        $filters['is_mandiri']
                    )
            )

            ->when(
                isset($filters['is_produk']),
                fn (Builder $q) =>
                    $q->where(
                        'lk_is_produk',
                        $filters['is_produk']
                    )
            );
    }
}