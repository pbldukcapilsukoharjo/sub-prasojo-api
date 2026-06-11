<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DistribusiWilayahCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_kecamatan' => $this['total_kecamatan'],

            'total_ajuan_dokumen' => $this['total_ajuan_dokumen'],

            'rata_rata_ajuan' => $this['rata_rata_ajuan'],

            'tabel' => [
                'wilayah' => $this['tabel']['wilayah'],

                'meta' => $this['tabel']['meta'],
            ]
        ];
    }
}