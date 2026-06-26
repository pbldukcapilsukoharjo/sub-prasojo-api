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

    public function applyMaster(Builder $query, array $filters): Builder
    {
        // Base Date filtering from BaseFilter equivalent
        if (!empty($filters['start_date'])) {
            $query->whereDate('ajuan_create_datetime', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('ajuan_create_datetime', '<=', $filters['end_date']);
        }

        if (!empty($filters['status_kategori'])) {
            $kategori = strtolower($filters['status_kategori']);
            if ($kategori === 'lembar_kerja') {
                $query->has('lembarKerjas');
            } elseif ($kategori === 'produk') {
                $query->has('produks');
            }
        }

        if (!empty($filters['id_kecamatan'])) {
            $query->where('ajuan_kecamatan_code', $filters['id_kecamatan']);
        }

        if (!empty($filters['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $filters['id_layanan']);
        }

        if (!empty($filters['status'])) {
            $query->where('ajuan_status', $filters['status']);
        }

        if (!empty($filters['pelapor'])) {
            $pelapor = strtolower($filters['pelapor']);
            if ($pelapor === 'online') {
                $query->where('ajuan_is_online', 1);
            } elseif ($pelapor === 'offline') {
                $query->where('ajuan_is_online', 0);
            } elseif ($pelapor === 'mandiri') {
                $query->where('ajuan_is_mandiri', 1);
            } elseif ($pelapor === 'operator') {
                $query->where('ajuan_is_mandiri', 0);
            } else {
                $query->where('ajuan_pelapor_role_name', 'LIKE', '%' . $filters['pelapor'] . '%');
            }
        }

        if (!empty($filters['search_no_reg'])) {
            $query->where('ajuan_no_reg', 'LIKE', '%' . $filters['search_no_reg'] . '%');
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ajuan_no_reg', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('ajuan_pelapor_nik', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }
}
