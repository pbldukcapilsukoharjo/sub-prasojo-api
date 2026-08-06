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

        if (!empty($this->request['id_kecamatan'])) {
            $query->where('ajuan_kecamatan_code', $this->request['id_kecamatan']);
        }

        if (!empty($this->request['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $this->request['id_layanan']);
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