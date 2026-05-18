<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class LembarKerjaCollection extends ResourceCollection
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        return [
            'items' => LembarKerjaResource::collection(
                $this->collection
            ),

            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}