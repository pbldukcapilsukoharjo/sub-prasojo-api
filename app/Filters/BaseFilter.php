<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    protected array $request;
    protected string $dateColumn = 'created_at';

    public function __construct(array $request = [])
    {
        $this->request = $request;
    }

    public function apply(Builder $query): Builder
    {
        $this->applyDateFilters($query);
        $this->applySorting($query);
        $this->applySearch($query);

        return $query;
    }

    protected function applyDateFilters(Builder $query): void
    {
        if (!empty($this->request['start_date']) && !empty($this->request['end_date'])) {
            $startDate = Carbon::createFromFormat('d-m-Y', $this->request['start_date'])->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', $this->request['end_date'])->endOfDay();
            $query->whereBetween($this->dateColumn, [$startDate, $endDate]);
        } elseif (!empty($this->request['periode_bulan'])) {
            $month = (int)$this->request['periode_bulan'];
            $query->whereMonth($this->dateColumn, $month)
                  ->whereYear($this->dateColumn, Carbon::now()->year);
        }
    }

    protected function applySorting(Builder $query): void
    {
        if (!empty($this->request['sort_by'])) {
            $direction = !empty($this->request['sort_dir']) && strtolower($this->request['sort_dir']) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($this->request['sort_by'], $direction);
        }
    }

    abstract protected function applySearch(Builder $query): void;
}
