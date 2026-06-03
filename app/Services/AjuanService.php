<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\AjuanFilter;
use App\Models\Ajuan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AjuanService
{
    public function __construct(
        protected AjuanFilter $filter
    ) {}

    public function getAll(
        array $filters
    ): LengthAwarePaginator {
        $query = Ajuan::query()
            ->with([
                'jenisAjuan',
                'pelapor',
            ]);

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return $query
            ->latest('ajuan_create_datetime')
            ->paginate(10);
    }

    public function getDetail(
        int $ajuanId
    ): Ajuan {
        return Ajuan::query()
            ->with([
                'jenisAjuan',
                'pelapor',
                'logStatuses',
            ])
            ->findOrFail($ajuanId);
    }
}