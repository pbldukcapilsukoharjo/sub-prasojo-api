<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UlasanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        $reviews = $this['reviews'];

        return [

            /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */
            'summary' => [

                'average_rating' =>
                    $this['summary']['average_rating'],

                'total_review' =>
                    $this['summary']['total_review'],

                'rating' =>
                    $this['summary']['rating'],
            ],

            /*
            |--------------------------------------------------------------------------
            | Reviews
            |--------------------------------------------------------------------------
            */
            'reviews' => [

                'list' => UlasanItemResource::collection(
                    $reviews->items()
                ),

                'meta' => [

                    'current_page' =>
                        $reviews->currentPage(),

                    'per_page' =>
                        $reviews->perPage(),

                    'total' =>
                        $reviews->total(),

                    'last_page' =>
                        $reviews->lastPage(),

                    'from' =>
                        $reviews->firstItem(),

                    'to' =>
                        $reviews->lastItem(),
                ],
            ],
        ];
    }
}