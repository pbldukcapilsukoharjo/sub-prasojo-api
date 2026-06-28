<?php

declare(strict_types=1);

namespace App\Http\Resources\Sla;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class SlaLayananCollection extends ResourceCollection
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => true,

            'data' => SlaLayananResource::collection(
                $this->collection
            ),

            'meta' => [
                'current_page' =>
                    $this->currentPage(),

                'per_page' =>
                    $this->perPage(),

                'total' =>
                    $this->total(),

                'last_page' =>
                    $this->lastPage(),
            ],
        ];
    }
}