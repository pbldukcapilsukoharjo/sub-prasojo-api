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

        $query->when(
            !empty($filters['periode']),
            fn (Builder $q)
                => $q->whereMonth('lk_create_datetime', $filters['periode'])
                     ->whereYear('lk_create_datetime', now()->year)
        );

        $query->when(
            !empty($filters['status']),
            fn (Builder $q)
                => $q->where('lk_status', $filters['status'])
        );

        $query->when(
            !empty($filters['layanan']),
            fn (Builder $q)
                => $q->where('lk_layanan_kode', $filters['layanan'])
        );

        $kecamatan = $filters['kecamatan'] ?? $filters['district'] ?? null;
        $query->when(
            !empty($kecamatan),
            function (Builder $q) use ($kecamatan) {
                $q->whereHas(
                    'ajuan',
                    fn (Builder $aq)
                        => $aq->where(
                            'ajuan_kecamatan_code', // use code instead of name based on previous implementations
                            $kecamatan
                        )->orWhere('ajuan_kecamatan_name', $kecamatan)
                );
            }
        );

        $pelapor = $filters['pelapor'] ?? $filters['reporter'] ?? null;
        $query->when(
            !empty($pelapor),
            function (Builder $q) use ($pelapor) {
                $pelaporLower = strtolower($pelapor);
                if ($pelaporLower === 'online') {
                    $q->where('lk_ajuan_is_online', 1);
                } elseif ($pelaporLower === 'offline') {
                    $q->where('lk_ajuan_is_online', 0);
                } elseif ($pelaporLower === 'mandiri') {
                    $q->where('lk_ajuan_is_mandiri', 1);
                } elseif ($pelaporLower === 'operator') {
                    $q->where('lk_ajuan_is_mandiri', 0);
                } else {
                    $q->where('lk_pelapor_role_name', 'like', "%{$pelapor}%");
                }
            }
        );

        $sort = strtolower($filters['sort'] ?? $filters['sort_by'] ?? 'terbaru');
        if ($sort === 'terlama' || $sort === 'oldest') {
            $query->orderBy('lk_create_datetime', 'asc');
        } else {
            $query->orderBy('lk_create_datetime', 'desc');
        }

        return $query;
    }
}