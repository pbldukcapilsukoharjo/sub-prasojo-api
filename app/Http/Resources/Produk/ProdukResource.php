<?php

declare(strict_types=1);

namespace App\Http\Resources\Produk;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProdukDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->prod_id,
            'no_regis' => $this->ajuan?->ajuan_no_reg,
            'nama' => $this->pelapor?->fullname,
            'nik' => $this->pelapor?->nik,
            'kode_ajuan' => $this->prod_layanan_kode,
            'no_kk' => $this->pelapor?->kk,
            'nama_identitas' => $this->pelapor?->fullname,
            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,
            'tanggal' => optional($this->prod_create_datetime)->format('Y-m-d'),
            'waktu' => optional($this->prod_create_datetime)->format('H:i'),
            'status' => $this->prod_status,
        ];
    }
}