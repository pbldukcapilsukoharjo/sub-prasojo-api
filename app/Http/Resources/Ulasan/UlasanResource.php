<?php

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UlasanResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'rata_rata_ulasan' =>
                $this['rata_rata_ulasan'],

            'total_ulasan' =>
                $this['total_ulasan'],

            'total_rating' =>
                $this['total_rating'],

            'daftar_ulasan' => [

                'list' =>
                    UlasanItemResource::collection(
                        $this['list']
                    ),

                'meta' => $this['meta']
            ]
        ];
    }
}