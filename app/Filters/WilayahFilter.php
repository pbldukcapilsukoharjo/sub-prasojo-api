<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class WilayahFilter extends BaseFilter
{
    protected string $dateColumn = 'ajuan_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['id_kecamatan'])) {
            $query->where('ajuan.ajuan_kecamatan_code', $this->request['id_kecamatan']);
        }

        $layanan = $this->request['id_layanan'] ?? $this->request['layanan_kode'] ?? null;
        if (!empty($layanan)) {
            $query->where('ajuan.ajuan_layanan_kode', $layanan);
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search'])) {
            $search = strtolower($this->request['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(ajuan.ajuan_kecamatan_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(ajuan.ajuan_kelurahan_name) LIKE ?', ["%{$search}%"]);
            });
        }
    }
}
