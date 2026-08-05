<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\ProdukFilter;
use App\Models\Prasojo\Produk;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final class ProdukService
{
    public function __construct(
        protected ProdukFilter $filter
    ) {}

    public function getAll(
        array $filters
    ): LengthAwarePaginator {
        try {
            $query = Produk::query()
                ->with([
                    'ajuan' => function($q) {
                        $q->with([
                            'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                            'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
                        ]);
                    },
                    'pelapor',
                ]);

            $query = $this->filter->apply(
                $query,
                $filters
            );

            return $query
                ->latest('prod_create_datetime')
                ->paginate(10);
        } catch (\Throwable $e) {
            Log::error('[ProdukService@getAll] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getDetail(int $produkId): Produk
    {
        try {
            return Produk::query()
                ->with([
                    'ajuan' => function($q) {
                        $q->with([
                            'aktaKelahiran', 'aktaKematian', 'datang', 'kia', 
                            'kk', 'ktpel', 'pindah', 'rekamJemput', 'updateData'
                        ]);
                    },
                    'pelapor',
                    'logStatuses' => function($q) {
                        $q->orderBy('log_create_datetime', 'asc');
                    }
                ])
                ->findOrFail($produkId);
        } catch (\Throwable $e) {
            Log::error('[ProdukService@getDetail] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}