<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LembarKerjaDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->lk_id,

            'no_regis' => $this->lk_ajuan_no_reg,

            'nama' => $this->ajuan?->pelapor?->fullname
                ?? $this->lk_pelapor_role_name,

            'nik' => $this->ajuan?->ajuan_pelapor_nik,

            'jenis_layanan' => $this->lk_layanan_kode,

            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,
            
            'kode_ajuan' => $this->lk_layanan_kode,
            'kode_produk' => $this->produk?->prod_layanan_kode,
            'jalur' => $this->lk_ajuan_is_online ? 'online' : 'offline',
            'pelapor' => $this->lk_pelapor_role_name,
            'status' => $this->lk_status,
            'tanggal' => $this->lk_create_datetime ? $this->lk_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
            'tanggal_parse' => $this->lk_create_datetime ? $this->lk_create_datetime->format('Y-m-d, H:i') : null,
        ];
    }
}