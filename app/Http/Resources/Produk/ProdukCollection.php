<?php

declare(strict_types=1);

namespace App\Http\Resources\Produk;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class ProdukCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => ProdukResource::collection($this->collection),
            'meta' => [
                'page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'total_page' => $this->lastPage(),
            ],
        ];
    }
}
