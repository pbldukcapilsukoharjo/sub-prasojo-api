<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UlasanItemResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => (int) $this->review_id,

            'nama' => 'Anonim',

            'layanan' => $this->ajuan?->layanan?->layanan_nama,

            'rating' => (int) $this->review_rating,

            'ulasan' => $this->review_content ?? '',

            'tanggal' => optional(
                $this->review_create_datetime
            )->format('d-m-Y'),

            'waktu' => optional(
                $this->review_create_datetime
            )->format('H:i'),
        ];
    }
}