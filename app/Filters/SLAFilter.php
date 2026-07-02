<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class SlaFilter extends BaseFilter
{
    protected string $dateColumn = 'ajuan_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['id_kecamatan'])) {
            $query->where('ajuan_kecamatan_code', $this->request['id_kecamatan']);
        }

        if (!empty($this->request['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $this->request['id_layanan']);
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        // Search spesifik tidak didefinisikan untuk SLA
    }

    protected function applySorting(Builder $query): void
    {
        // SLA sorting is handled manually in SLAService on the collection level,
        // so we don't apply sorting to the database query builder to avoid SQL errors.
    }
}