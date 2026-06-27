<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UlasanItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => (int) $this->review_id,

            'nama' => 'Anonim',

            'layanan' => $this->ajuan?->layanan?->layanan_nama,

            'nomor_registrasi' => $this->ajuan?->ajuan_no_reg,

            'rating' => (int) $this->review_rating,

            'ulasan' => $this->review_content,

            'tanggal' => $this->review_create_datetime?->format('d-m-Y'),

            'waktu' => $this->review_create_datetime?->format('H:i'),
        ];
    }
}