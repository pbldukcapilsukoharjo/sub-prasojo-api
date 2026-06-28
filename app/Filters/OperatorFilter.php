<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class OperatorFilter extends BaseFilter
{
    // The date filter typically applies to the ajuan table when calculating performance.
    // Ensure the query builder uses leftJoin('ajuan', ...) if date filters are applied.
    protected string $dateColumn = 'ajuan.ajuan_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['id_kecamatan'])) {
            // Apply to admin table
            $query->where('admin.kecamatan_code', $this->request['id_kecamatan']);
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search'])) {
            $searchTerm = '%' . $this->request['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('admin.fullname', 'like', $searchTerm)
                  ->orWhere('admin.kelurahan_name', 'like', $searchTerm)
                  ->orWhere('admin.kecamatan_name', 'like', $searchTerm);
            });
        }
    }
}
