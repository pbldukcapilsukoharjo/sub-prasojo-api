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

    public function getAll(
        array $filters
    ): LengthAwarePaginator {

        $query = LembarKerja::query()
            ->with([
                'ajuan.pelapor',
                'produk',
            ]);

        $query = $this->filter->apply(
            $query,
            $filters
        );

        $sortBy = $filters['sortBy']
            ?? 'newest';

        if ($sortBy === 'oldest') {

            $query->orderBy(
                'lk_create_datetime',
                'asc'
            );

        } else {

            $query->orderByDesc(
                'lk_create_datetime'
            );
        }

        return $query->paginate(10);
    }

    public function getDetail(
        int $lkId
    ): LembarKerja {

        return LembarKerja::query()
            ->with([
                'ajuan.pelapor',
                'produk',
            ])
            ->findOrFail($lkId);
    }
}