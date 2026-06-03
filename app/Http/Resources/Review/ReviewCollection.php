<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class ReviewCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => ReviewResource::collection(
                $this->collection
            ),
            'meta' => [
                'page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'total_page' => $this->lastPage(),
            ],
        ];
    }
}