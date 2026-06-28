<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UlasanResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(
        Request $request
    ): array {

        $summary = $this->resource['summary'];

        $reviews = $this->resource['reviews'];

        return [

            'rata_rata_ulasan' => (float) $summary['average_rating'],

            'total_ulasan' => (int) $summary['total_review'],

            'total_rating' => [

                '1' => $summary['rating'][1],

                '2' => $summary['rating'][2],

                '3' => $summary['rating'][3],

                '4' => $summary['rating'][4],

                '5' => $summary['rating'][5],

            ],

            'daftar_ulasan' => [

                'list' => UlasanItemResource::collection(
                    $reviews->items()
                ),

                'meta' => [

                    'page' => $reviews->currentPage(),

                    'per_page' => $reviews->perPage(),

                    'total' => $reviews->total(),

                    'total_page' => $reviews->lastPage(),

                ],
            ],
        ];
    }
}