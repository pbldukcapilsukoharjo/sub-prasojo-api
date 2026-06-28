<?php

declare(strict_types=1);

namespace App\Http\Resources\DistribusiWilayah;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistribusiWilayahItemResource extends JsonResource
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

            'desa' =>
                $this['desa'],

            'kecamatan' =>
                $this['kecamatan'],

            'total_ajuan' =>
                $this['total_ajuan'],

            'ktp-el' =>
                $this['ktp-el'],

            'kia' =>
                $this['kia'],

            'akta_kelahiran' =>
                $this['akta_kelahiran'],

            'akta_kematian' =>
                $this['akta_kematian'],

            'perpindahan' =>
                $this['perpindahan'],

            'kedatangan' =>
                $this['kedatangan'],

            'update_data' =>
                $this['update_data'],

            'rekam_jemput_bola' =>
                $this['rekam_jemput_bola'],

        ];
    }
}