<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LembarKerjaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        return [
            'lk_id' => $this->lk_id,
            'lk_ajuan_id' => $this->lk_ajuan_id,
            'lk_ajuan_no_reg' => $this->lk_ajuan_no_reg,
            'lk_jenis_ajuan_id' => $this->lk_jenis_ajuan_id,
            'lk_layanan_kode' => $this->lk_layanan_kode,
            'lk_status' => $this->lk_status,

            'ajuan' => $this->whenLoaded('ajuan'),

            'produk' => $this->whenLoaded('produk'),

            'created_at' => $this->lk_create_datetime,
        ];
    }
}