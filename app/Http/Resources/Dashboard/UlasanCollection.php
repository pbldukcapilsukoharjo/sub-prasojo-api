<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UlasanCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rata_rata_bintang' => $this['rata_rata_bintang'],

            'total_ulasan' => $this['total_ulasan'],

            'total_bintang' => $this['total_bintang'],

            'tabel' => [
                'ulasan' => $this['tabel']['ulasan'],

                'meta' => $this['tabel']['meta'],
            ]
        ];
    }
}