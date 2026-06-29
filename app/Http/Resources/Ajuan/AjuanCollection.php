<?php

declare(strict_types=1);

namespace App\Http\Resources\Ajuan;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class AjuanCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => AjuanResource::collection($this->collection),
            'meta' => [
                'page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'total_page' => $this->lastPage(),
            ],
        ];
    }
}
