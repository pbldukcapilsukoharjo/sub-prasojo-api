<?php

declare(strict_types=1);

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OperatorPeringkatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'list' => $this->resource->items(),
            'meta' => [
                'page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'total_page' => $this->resource->lastPage(),
            ],
        ];
    }
}
