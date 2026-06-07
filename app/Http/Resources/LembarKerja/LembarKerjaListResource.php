<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LembarKerjaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->lk_id,

            'no_regis' => $this->lk_ajuan_no_reg,

            'kode_produk' => $this->produk?->produk_kode
                ?? $this->lk_layanan_kode,

            'kode_ajuan' => $this->lk_layanan_kode,

            'jalur' => (
                $this->ajuan?->ajuan_is_online ?? 0
            )
                ? 'online'
                : 'offline',

            'pelapor' => $this->ajuan?->ajuan_pelapor_role_name,

            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,

            'tanggal' => optional(
                $this->lk_create_datetime
            )->format('Y-m-d'),

            'waktu' => optional(
                $this->lk_create_datetime
            )->format('H:i'),

            'status' => $this->lk_status,
        ];
    }
}