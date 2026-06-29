<?php

declare(strict_types=1);

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLAResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'rata_rata_waktu_proses' =>
                $this['rata_rata_waktu_proses'],

            'pencapaian_sla' =>
                $this['pencapaian_sla'],

            'target_sla' =>
                $this['target_sla'],

            'jumlah_ajuan' =>
                $this['jumlah_ajuan'],

            'daftar_rincian' => [

                'list' =>
                    SLADetailResource::collection(
                        collect(
                            $this['daftar_rincian']['list']
                        )
                    ),

                'meta' =>
                    $this['daftar_rincian']['meta'],
            ],
        ];
    }
}