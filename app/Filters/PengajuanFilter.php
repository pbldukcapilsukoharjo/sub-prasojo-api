<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class PengajuanFilter extends BaseFilter
{
    protected string $dateColumn = 'ajuan_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        $this->applyStatusKategori($query);
        $this->applyKecamatan($query);
        $this->applyLayanan($query);
        $this->applyStatus($query);
        $this->applyPelapor($query);

        return $query;
    }

    protected function applyStatusKategori(Builder $query): void
    {
        if (!empty($this->request['status_kategori'])) {
            $kategori = strtolower($this->request['status_kategori']);
            if ($kategori === 'lembar_kerja') {
                $query->has('lembarKerjas');
            } elseif ($kategori === 'produk') {
                $query->has('produks');
            }
        }
    }

    protected function applyKecamatan(Builder $query): void
    {
        if (!empty($this->request['id_kecamatan'])) {
            $query->where('ajuan_kecamatan_code', $this->request['id_kecamatan']);
        }
    }

    protected function applyLayanan(Builder $query): void
    {
        if (!empty($this->request['id_layanan'])) {
            $query->where('ajuan_layanan_kode', $this->request['id_layanan']);
        }
    }

    protected function applyStatus(Builder $query): void
    {
        if (!empty($this->request['status'])) {
            $query->where('ajuan_status', $this->request['status']);
        }
    }

    protected function applyPelapor(Builder $query): void
    {
        if (!empty($this->request['pelapor'])) {
            $pelapor = strtolower($this->request['pelapor']);
            if ($pelapor === 'online') {
                $query->where('ajuan_is_online', 1);
            } elseif ($pelapor === 'offline') {
                $query->where('ajuan_is_online', 0);
            } elseif ($pelapor === 'mandiri') {
                $query->where('ajuan_is_mandiri', 1);
            } elseif ($pelapor === 'operator') {
                $query->where('ajuan_is_mandiri', 0);
            } else {
                $query->where('ajuan_pelapor_role_name', 'LIKE', '%' . $this->request['pelapor'] . '%');
            }
        }
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search_no_reg'])) {
            $query->where('ajuan_no_reg', 'LIKE', '%' . $this->request['search_no_reg'] . '%');
        }

        // Generic search for pelapor nik/name if needed (using search query)
        if (!empty($this->request['search'])) {
            $query->where(function ($q) {
                $q->where('ajuan_no_reg', 'LIKE', '%' . $this->request['search'] . '%')
                  ->orWhere('ajuan_pelapor_nik', 'LIKE', '%' . $this->request['search'] . '%');
            });
        }
    }
}
