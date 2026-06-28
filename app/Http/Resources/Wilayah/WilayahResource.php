<?php

namespace App\Http\Resources\Wilayah;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WilayahResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'desa' => $this->desa,
            'kecamatan' => $this->kecamatan,
            'total_ajuan' => $this->total_ajuan,
            'ktp_el' => $this->ktp_el ?? 0,
            'kia' => $this->kia ?? 0,
            'akta_kelahiran' => $this->akta_kelahiran ?? 0,
            'akta_kematian' => $this->akta_kematian ?? 0,
            'perpindahan' => $this->perpindahan ?? 0,
            'kedatangan' => $this->kedatangan ?? 0,
            'update_data' => $this->update_data ?? 0,
            'rekam_jemput_bola' => $this->rekam_jemput_bola ?? 0,
        ];
    }
}