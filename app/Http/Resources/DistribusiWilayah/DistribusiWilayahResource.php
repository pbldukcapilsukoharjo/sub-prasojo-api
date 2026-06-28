<?php

declare(strict_types=1);

namespace App\Http\Resources\DistribusiWilayah;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistribusiWilayahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        $items = $this['items'];

        return [

            'total_kecamatan' =>
                $this['summary']['total_kecamatan'],

            'total_ajuan_dokumen' =>
                $this['summary']['total_ajuan_dokumen'],

            'rata_rata_ajuan' =>
                $this['summary']['rata_rata_ajuan'],

            'daftar_ajuan' => [

                'list' => DistribusiWilayahItemResource::collection(
                    $items->items()
                ),

                'meta' => [

                    'page' =>
                        $items->currentPage(),

                    'per_page' =>
                        $items->perPage(),

                    'total' =>
                        $items->total(),

                    'total_page' =>
                        $items->lastPage(),
                ],
            ],
        ];
    }
}