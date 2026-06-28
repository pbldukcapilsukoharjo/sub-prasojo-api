<?php

namespace App\Http\Resources\PeringkatOperator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeringkatOperatorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'total_layanan' =>
                $this['total_layanan'],

            'rata_rata_durasi' =>
                $this['rata_rata_durasi'],

            'tingkat_selesai' =>
                $this['tingkat_selesai'],

            'peringkat_operator' => [

                'list' =>
                    PeringkatOperatorItemResource::collection(
                        collect(
                            $this['peringkat_operator']['list']
                        )
                    ),

                'meta' =>
                    $this['peringkat_operator']['meta'],
            ],
        ];
    }
}