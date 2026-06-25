<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class DashboardFilter extends BaseFilter
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
        // Untuk Dashboard, pencarian teks bebas (search) tidak didefinisikan secara khusus
    }
}