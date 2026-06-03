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
            'no_regis' => $this->prod_ajuan_no_reg,
            'nama_produk' => $this->prod_nama,
            'nomor_produk' => $this->prod_nomor,
            'kode_layanan' => $this->prod_layanan_kode,
            'status' => $this->prod_status,
            'url' => $this->prod_url,
            'tanggal' => optional($this->prod_create_datetime)
                ->format('Y-m-d H:i'),
        ];
    }
}