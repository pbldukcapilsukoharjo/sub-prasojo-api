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
