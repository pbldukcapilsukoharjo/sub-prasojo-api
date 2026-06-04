<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\ProdukFilter;
use App\Models\Produk;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProdukService
{
    public function __construct(
        protected ProdukFilter $filter
    ) {}

    public function getAll(
        array $filters
    ): LengthAwarePaginator {
        $query = Produk::query()
            ->with([
                'ajuan',
                'pelapor',
            ]);

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return $query
            ->latest('prod_create_datetime')
            ->paginate(10);
    }

    public function getDetail(int $produkId): Produk
    {
        return Produk::query()
            ->with([
                'ajuan',
                'pelapor',
                'logStatuses'
            ])
            ->findOrFail($produkId);
    }
}