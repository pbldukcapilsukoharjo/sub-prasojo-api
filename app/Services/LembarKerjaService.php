<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\LembarKerjaFilter;
use App\Models\LembarKerja;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LembarKerjaService
{
    public function __construct(
        protected LembarKerjaFilter $filter
    ) {}

    /**
     * Get all lembar kerja.
     */
    public function getAll(
        array $filters
    ): LengthAwarePaginator {
        $query = LembarKerja::query()
            ->with([
                'ajuan',
                'produk',
            ]);

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return $query
            ->latest()
            ->paginate(10);
    }

    /**
     * Get detail lembar kerja.
     */
    public function getDetail(
        int $lkId
    ): LembarKerja {
        return LembarKerja::query()
            ->with([
                'ajuan',
                'produk',
            ])
            ->findOrFail($lkId);
    }
}