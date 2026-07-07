<?php

declare(strict_types=1);

namespace App\Http\Resources\Produk;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProdukResource extends JsonResource
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
            'nomor' => $this->prod_nomor,
            'nama_identitas_produk' => $this->prod_nama,
            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,
            'tanggal' => $this->prod_create_datetime ? $this->prod_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
            'tanggal_parse' => $this->prod_create_datetime ? $this->prod_create_datetime->format('Y-m-d, H:i') : null,
            'status' => $this->prod_status,
        ];
    }
}