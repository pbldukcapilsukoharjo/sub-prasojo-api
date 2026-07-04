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
        $query->when(
            !empty($filters['search']),
            function (Builder $q) use ($filters): void {
                $q->where(function (Builder $query) use ($filters): void {
                    $query
                        ->where('prod_ajuan_no_reg', 'like', "%{$filters['search']}%")
                        ->orWhere('prod_nama', 'like', "%{$filters['search']}%")
                        ->orWhere('prod_nomor', 'like', "%{$filters['search']}%");
                });
            }
        );

        $kecamatan = $filters['kecamatan'] ?? $filters['district'] ?? null;
        $query->when(
            !empty($kecamatan),
            fn (Builder $q) =>
                $q->whereHas(
                    'ajuan',
                    fn (Builder $aq) =>
                        $aq->where('ajuan_kecamatan_code', $kecamatan)
                           ->orWhere('ajuan_kecamatan_name', $kecamatan)
                )
        );

        $query->when(
            !empty($filters['nama_identitas_produk']),
            fn (Builder $q) =>
                $q->where('prod_nama', 'like', "%{$filters['nama_identitas_produk']}%")
        );

        $query->when(
            !empty($filters['nomor_produk']),
            fn (Builder $q) =>
                $q->where('prod_nomor', $filters['nomor_produk'])
        );

        $query->when(
            !empty($filters['layanan']),
            fn (Builder $q) =>
                $q->where('prod_layanan_kode', $filters['layanan'])
        );

        // Not using start_date and end_date for Produk as per analysis, but to prevent breaking frontend 
        // if they still send it, we could leave it. Actually the analysis said:
        // "Produk TIDAK punya filter Pelapor, Status, maupun Rentang Tanggal."
        // We will just filter by periode.
        $query->when(
            !empty($filters['periode']),
            fn (Builder $q) =>
                $q->whereMonth('prod_create_datetime', $filters['periode'])
                  ->whereYear('prod_create_datetime', now()->year)
        );

        $sort = strtolower($filters['sort'] ?? $filters['sort_by'] ?? 'terbaru');
        if ($sort === 'terlama' || $sort === 'oldest') {
            $query->orderBy('prod_create_datetime', 'asc');
        } else {
            $query->orderBy('prod_create_datetime', 'desc');
        }

        return $query;
    }
}