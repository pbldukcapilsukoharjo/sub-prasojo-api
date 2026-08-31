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
            $layananKode = \App\Models\Prasojo\Layanan::resolveKode($layanan);
            $query->where('ajuan_layanan_kode', $layananKode);
        }

        $pelapor = $this->request['pelapor'] ?? $this->request['reporter'] ?? $this->request['id_pelapor'] ?? null;
        if (!empty($pelapor)) {
            $pelaporLower = strtolower($pelapor);
            if ($pelaporLower === 'online') {
                $query->where('ajuan_is_online', 1);
            } elseif ($pelaporLower === 'offline') {
                $query->where('ajuan_is_online', 0);
            } elseif ($pelaporLower === 'mandiri') {
                $query->where('ajuan_is_mandiri', 1);
            } elseif ($pelaporLower === 'operator') {
                $query->where('ajuan_is_mandiri', 0);
            } elseif ($pelaporLower === 'tamat') {
                $query->whereRaw('UPPER(TRIM(ajuan_keterangan)) = ?', ['TAMAT']);
            } else {
                $query->where('ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
            }
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        // Untuk Dashboard, pencarian teks bebas (search) tidak didefinisikan secara khusus
    }
}