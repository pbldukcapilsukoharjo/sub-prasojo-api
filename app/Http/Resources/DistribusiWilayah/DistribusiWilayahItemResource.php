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

            'id' => md5(
                $this->ajuan_kelurahan_code .
                $this->ajuan_kecamatan_code
            ),

            'desa' =>
                $this->ajuan_kelurahan_name,

            'kecamatan' =>
                $this->ajuan_kecamatan_name,

            'total_ajuan' =>
                (int) $this->total_ajuan,

            'ktp-el' =>
                (int) $this->ktp_el,

            'kia' =>
                (int) $this->kia,

            'akta_kelahiran' =>
                (int) $this->akta_kelahiran,

            'akta_kematian' =>
                (int) $this->akta_kematian,

            'perpindahan' =>
                (int) $this->perpindahan,

            'kedatangan' =>
                (int) $this->kedatangan,

            'update_data' =>
                (int) $this->update_data,

            'rekam_jemput_bola' =>
                (int) $this->rekam_jemput_bola,
        ];
    }
}