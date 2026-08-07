<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class UlasanFilter extends BaseFilter
{
    protected string $dateColumn = 'review_create_datetime';

    public function apply(Builder $query): Builder
    {
        parent::apply($query);

        if (!empty($this->request['layanan_kode'])) {
            $query->whereHas('ajuan', function ($q) {
                $q->where('ajuan_layanan_kode', $this->request['layanan_kode']);
            });
        }

        if (!empty($this->request['rating'])) {
            $query->where('review_rating', (int)$this->request['rating']);
        }

        $pelapor = $this->request['pelapor'] ?? $this->request['reporter'] ?? $this->request['id_pelapor'] ?? null;
        if (!empty($pelapor)) {
            $query->whereHas('ajuan', function (Builder $q) use ($pelapor) {
                $pelaporLower = strtolower($pelapor);
                if ($pelaporLower === 'online') {
                    $q->where('ajuan_is_online', 1);
                } elseif ($pelaporLower === 'offline') {
                    $q->where('ajuan_is_online', 0);
                } elseif ($pelaporLower === 'mandiri') {
                    $q->where('ajuan_is_mandiri', 1);
                } elseif ($pelaporLower === 'operator') {
                    $q->where('ajuan_is_mandiri', 0);
                } elseif ($pelaporLower === 'tamat') {
                    $q->whereRaw('UPPER(TRIM(ajuan_keterangan)) = ?', ['TAMAT']);
                } else {
                    $q->where('ajuan_pelapor_role_name', 'like', "%{$pelapor}%");
                }
            });
        }

        return $query;
    }

    protected function applySearch(Builder $query): void
    {
        if (!empty($this->request['search'])) {
            $search = $this->request['search'];
            $query->where(function ($q) use ($search) {
                $q->where('review_content', 'like', "%{$search}%")
                  ->orWhereHas('ajuan', function ($q2) use ($search) {
                      $q2->where('ajuan_no_reg', 'like', "%{$search}%");
                  });
            });
        }
    }

    protected function applySorting(Builder $query): void
    {
        if (!empty($this->request['sort_by'])) {
            switch ($this->request['sort_by']) {
                case 'newest':
                    $query->orderBy($this->dateColumn, 'desc');
                    break;
                case 'oldest':
                    $query->orderBy($this->dateColumn, 'asc');
                    break;
                case 'rating_asc':
                    $query->orderBy('review_rating', 'asc');
                    break;
                case 'rating_desc':
                    $query->orderBy('review_rating', 'desc');
                    break;
            }
        } else {
            // Default sorting
            $query->orderBy($this->dateColumn, 'desc');
        }
    }
}