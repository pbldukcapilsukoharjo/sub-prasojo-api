<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class OperatorFilter extends BaseFilter
{
    protected string $dateColumn = 'log_ajuan_status.log_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['id_kecamatan'])) {
            $kec = $this->request['id_kecamatan'];
            $query->where(function (Builder $builder) use ($kec) {
                $builder->where('admin.kecamatan_name', $kec)
                        ->orWhere('ajuan.ajuan_kecamatan_code', $kec)
                        ->orWhere('ajuan.ajuan_kecamatan_name', $kec);
            });
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
            $search = $this->request['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('admin.fullname', 'like', "%{$search}%")
                        ->orWhere('admin.username', 'like', "%{$search}%");
            });
        }
    }
}
