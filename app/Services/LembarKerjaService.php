<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\LembarKerjaFilter;
use App\Models\Prasojo\LembarKerja;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final class LembarKerjaService
{
    public function __construct(
        protected LembarKerjaFilter $filter
    ) {}

    public function getAll(
        array $filters
    ): LengthAwarePaginator {
        try {
            $query = LembarKerja::query()
                ->with([
                    'ajuan.pelapor',
                    'produk',
                ]);

            $query = $this->filter->apply(
                $query,
                $filters
            );

            $sort_by = $filters['sort_by']
                ?? 'newest';

            if ($sort_by === 'oldest') {

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
        } catch (\Throwable $e) {
            Log::error('[LembarKerjaService@getAll] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getDetail(
        int $lkId
    ): LembarKerja {
        try {
            return LembarKerja::query()
                ->with([
                    'ajuan.pelapor',
                    'produk',
                ])
                ->findOrFail($lkId);
        } catch (\Throwable $e) {
            Log::error('[LembarKerjaService@getDetail] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}