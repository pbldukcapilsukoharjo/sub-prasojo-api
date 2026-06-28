<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UlasanKpiResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'rata_rata_bintang' => (float) $this['average_rating'],

            'distribusi' => [

                'bintang_5' => (int) $this['rating'][5],

                'bintang_4' => (int) $this['rating'][4],

                'bintang_3' => (int) $this['rating'][3],

                'bintang_2' => (int) $this['rating'][2],

                'bintang_1' => (int) $this['rating'][1],

            ],
        ];
    }
}