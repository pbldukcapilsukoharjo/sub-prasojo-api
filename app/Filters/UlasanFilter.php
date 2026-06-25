<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class UlasanFilter extends BaseFilter
{
    protected string $dateColumn = 'review_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['id_layanan'])) {
            $query->whereHas('ajuan', function ($q) {
                $q->where('ajuan_layanan_kode', $this->request['id_layanan']);
            });
        }

        if (!empty($this->request['rating'])) {
            $query->where('review_rating', (int)$this->request['rating']);
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search'])) {
            $search = $this->request['search'];
            $query->where(function ($q) use ($search) {
                $q->where('review_content', 'like', "%{$search}%")
                  ->orWhereHas('ajuan', function ($q2) use ($search) {
                      $q2->where('ajuan_no_reg', 'like', "%{$search}%");
                  });
            });
        }
    }
}