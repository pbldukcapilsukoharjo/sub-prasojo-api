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

        $layanan = $this->request['id_layanan'] ?? $this->request['layanan_kode'] ?? $this->request['layanan'] ?? null;
        if (!empty($layanan)) {
            $layananKode = \App\Models\Prasojo\Layanan::resolveKode($layanan);
            $query->where('ajuan.ajuan_layanan_kode', $layananKode);
        }

        $pelapor = $this->request['pelapor'] ?? $this->request['reporter'] ?? $this->request['id_pelapor'] ?? null;
        if (!empty($pelapor)) {
            $pelaporLower = strtolower($pelapor);
            if ($pelaporLower === 'online') {
                $query->where('ajuan.ajuan_is_online', 1);
            } elseif ($pelaporLower === 'offline') {
                $query->where('ajuan.ajuan_is_online', 0);
            } elseif ($pelaporLower === 'mandiri') {
                $query->where('ajuan.ajuan_is_mandiri', 1);
            } elseif ($pelaporLower === 'operator') {
                $query->where('ajuan.ajuan_is_mandiri', 0);
            } elseif ($pelaporLower === 'tamat') {
                $query->whereRaw('UPPER(TRIM(ajuan.ajuan_keterangan)) = ?', ['TAMAT']);
            } else {
                $query->where('ajuan.ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
            }
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
