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
        $query->when(
            !empty($filters['search']),
            function (Builder $q) use ($filters): void {
                $q->where(function (Builder $query) use ($filters): void {
                    $query
                        ->where('ajuan_no_reg', 'like', "%{$filters['search']}%")
                        ->orWhere('ajuan_pelapor_nik', 'like', "%{$filters['search']}%");
                });
            }
        );

        $kecamatan = $filters['kecamatan'] ?? $filters['district'] ?? null;
        $query->when(
            !empty($kecamatan),
            fn (Builder $q) =>
                $q->where('ajuan_kecamatan_code', $kecamatan)
                  ->orWhere('ajuan_kecamatan_name', $kecamatan)
        );

        $query->when(
            isset($filters['status']),
            fn (Builder $q) =>
                $q->where('ajuan_status', $filters['status'])
        );

        $query->when(
            !empty($filters['layanan']),
            fn (Builder $q) =>
                $q->where('ajuan_layanan_kode', $filters['layanan'])
        );

        $query->when(
            !empty($filters['jenis_ajuan']),
            fn (Builder $q) =>
                $q->where('ajuan_jenis_ajuan_id', $filters['jenis_ajuan'])
        );

        $query->when(
            isset($filters['jalur']),
            function (Builder $q) use ($filters) {
                $jalur = $filters['jalur'];
                if (is_numeric($jalur)) {
                     $q->where('ajuan_is_online', $jalur);
                } else {
                     $jalurLower = strtolower((string)$jalur);
                     if ($jalurLower === 'online') {
                         $q->where('ajuan_is_online', 1);
                     } elseif ($jalurLower === 'offline') {
                         $q->where('ajuan_is_online', 0);
                     }
                }
            }
        );

        $pelapor = $filters['pelapor'] ?? $filters['reporter'] ?? null;
        $query->when(
            !empty($pelapor),
            function (Builder $q) use ($pelapor) {
                $pelaporLower = strtolower($pelapor);
                if ($pelaporLower === 'online') {
                    $q->where('ajuan_is_online', 1);
                } elseif ($pelaporLower === 'offline') {
                    $q->where('ajuan_is_online', 0);
                } elseif ($pelaporLower === 'mandiri') {
                    $q->where('ajuan_is_mandiri', 1);
                } elseif ($pelaporLower === 'operator') {
                    $q->where('ajuan_is_mandiri', 0);
                } else {
                    $q->where('ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
                }
            }
        );

        $query->when(
            !empty($filters['start_date']),
            fn (Builder $q) =>
                $q->whereDate('ajuan_create_datetime', '>=', $filters['start_date'])
        );

        $query->when(
            !empty($filters['end_date']),
            fn (Builder $q) =>
                $q->whereDate('ajuan_create_datetime', '<=', $filters['end_date'])
        );

        $query->when(
            !empty($filters['periode']),
            fn (Builder $q) =>
                $q->whereMonth('ajuan_create_datetime', $filters['periode'])
                  ->whereYear('ajuan_create_datetime', now()->year)
        );

        $sort = strtolower($filters['sort'] ?? $filters['sort_by'] ?? 'terbaru');
        if ($sort === 'terlama' || $sort === 'oldest') {
            $query->orderBy('ajuan_create_datetime', 'asc');
        } else {
            $query->orderBy('ajuan_create_datetime', 'desc');
        }

        return $query;
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

        if (isset($filters['status'])) {
            $query->where('ajuan_status', $filters['status']);
        }

        if (!empty($filters['jenis_ajuan'])) {
            $query->where('ajuan_jenis_ajuan_id', $filters['jenis_ajuan']);
        }

        if (isset($filters['jalur'])) {
            $jalur = $filters['jalur'];
            if (is_numeric($jalur)) {
                $query->where('ajuan_is_online', $jalur);
            } else {
                $jalurLower = strtolower((string)$jalur);
                if ($jalurLower === 'online') {
                    $query->where('ajuan_is_online', 1);
                } elseif ($jalurLower === 'offline') {
                    $query->where('ajuan_is_online', 0);
                }
            }
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
