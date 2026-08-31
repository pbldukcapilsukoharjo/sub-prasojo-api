<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class SLAFilter extends BaseFilter
{
    protected string $dateColumn = 'ajuan_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        $kecamatan = $this->request['id_kecamatan'] ?? $this->request['kecamatan'] ?? null;
        if (!empty($kecamatan)) {
            $query->where(function (Builder $q) use ($kecamatan): void {
                $q->where('ajuan_kecamatan_code', $kecamatan)
                  ->orWhere('ajuan_kecamatan_name', $kecamatan);
            });
        }

        $layanan = $this->request['id_layanan'] ?? $this->request['layanan'] ?? null;
        if (!empty($layanan)) {
            $layananKode = \App\Models\Prasojo\Layanan::resolveKode($layanan);
            $query->where('ajuan_layanan_kode', $layananKode);
        }

        if (!empty($this->request['jenis_ajuan'])) {
            $query->where('ajuan_jenis_ajuan_id', $this->request['jenis_ajuan']);
        }

        if (isset($this->request['jalur'])) {
            $jalur = $this->request['jalur'];
            if (is_numeric($jalur)) {
                $query->where('ajuan_is_online', $jalur);
            } else {
                $jalurLower = strtolower((string) $jalur);
                if ($jalurLower === 'online') {
                    $query->where('ajuan_is_online', 1);
                } elseif ($jalurLower === 'offline') {
                    $query->where('ajuan_is_online', 0);
                }
            }
        }

        if (!empty($this->request['pelapor'])) {
            $pelapor = strtolower((string) $this->request['pelapor']);
            if ($pelapor === 'online') {
                $query->where('ajuan_is_online', 1);
            } elseif ($pelapor === 'offline') {
                $query->where('ajuan_is_online', 0);
            } elseif ($pelapor === 'mandiri') {
                $query->where('ajuan_is_mandiri', 1);
            } elseif ($pelapor === 'operator') {
                $query->where('ajuan_is_mandiri', 0);
            } elseif ($pelapor === 'tamat') {
                $query->whereRaw('UPPER(TRIM(ajuan_keterangan)) = ?', ['TAMAT']);
            } else {
                $query->where('ajuan_pelapor_role_name', 'LIKE', '%' . $this->request['pelapor'] . '%');
            }
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search'])) {
            $search = $this->request['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('ajuan_no_reg', 'like', "%{$search}%")
                  ->orWhere('ajuan_pelapor_nik', 'like', "%{$search}%");
            });
        }
    }

    protected function applySorting(Builder $query): void
    {
        // SLA sorting is handled manually in SLAService on the collection level,
        // so we don't apply sorting to the database query builder to avoid SQL errors.
    }
}