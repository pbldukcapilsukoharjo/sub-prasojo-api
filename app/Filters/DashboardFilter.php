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

        $kecamatan = $this->request['id_kecamatan'] ?? $this->request['kecamatan'] ?? null;
        if (!empty($kecamatan)) {
            $query->where('ajuan_kecamatan_code', $kecamatan);
        }

        $layanan = $this->request['id_layanan'] ?? $this->request['layanan'] ?? null;
        if (!empty($layanan)) {
            $query->where('ajuan_layanan_kode', $layanan);
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        // Untuk Dashboard, pencarian teks bebas (search) tidak didefinisikan secara khusus
    }
}