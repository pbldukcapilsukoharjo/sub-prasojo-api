<?php

namespace App\Http\Resources\PeringkatOperator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeringkatOperatorItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                $this['id'],

            'peringkat' =>
                $this['peringkat'],

            'operator' =>
                $this['operator'],

            'desa' =>
                $this['desa'],

            'kecamatan' =>
                $this['kecamatan'],

            'jumlah_ajuan' =>
                $this['jumlah_ajuan'],
        ];
    }
}