<?php

namespace App\Filters;

class SLAFilter
{
    public function transform(array $params): array
    {
        return [
            'page'      => $params['page'] ?? 1,
            'search'    => $params['search'] ?? null,
            'district'  => $params['district'] ?? null,
            'period'    => $params['period'] ?? null,
            'sortBy'    => $params['sortBy'] ?? 'newest',
            'startDate' => $params['startDate'] ?? null,
            'endDate'   => $params['endDate'] ?? null,
        ];
    }
}