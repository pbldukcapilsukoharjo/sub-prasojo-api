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

        return [

            'total_kecamatan' =>
                $this['total_kecamatan'],

            'total_ajuan_dokumen' =>
                $this['total_ajuan_dokumen'],

            'rata_rata_ajuan' =>
                $this['rata_rata_ajuan'],

            'daftar_ajuan' => [

                'list' =>
                    DistribusiWilayahItemResource::collection(
                        collect(
                            $this['daftar_ajuan']['list']
                        )
                    ),

                'meta' =>
                    $this['daftar_ajuan']['meta'],

            ],

        ];
    }
}