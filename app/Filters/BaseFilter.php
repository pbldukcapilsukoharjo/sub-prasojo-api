<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    public array $request;
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

    protected function parseDate(string $value, bool $isEnd = false): Carbon
    {
        try {
            $date = Carbon::createFromFormat('d-m-Y', $value);
        } catch (\Throwable) {
            $date = Carbon::parse($value);
        }

        return $isEnd ? $date->endOfDay() : $date->startOfDay();
    }

    protected function applyDateFilters(Builder $query): void
    {
        if (!empty($this->request['start_date']) && !empty($this->request['end_date'])) {
            $start_date = $this->parseDate($this->request['start_date']);
            $end_date = $this->parseDate($this->request['end_date'], true);
            $query->whereBetween($this->dateColumn, [$start_date, $end_date]);
        } elseif (!empty($this->request['start_date'])) {
            $start_date = $this->parseDate($this->request['start_date']);
            $query->whereDate($this->dateColumn, '>=', $start_date);
        } elseif (!empty($this->request['end_date'])) {
            $end_date = $this->parseDate($this->request['end_date'], true);
            $query->whereDate($this->dateColumn, '<=', $end_date);
        }

        $periode = $this->request['periode_bulan'] ?? $this->request['periode'] ?? null;
        if (!empty($periode)) {
            $month = (int) $periode;
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
