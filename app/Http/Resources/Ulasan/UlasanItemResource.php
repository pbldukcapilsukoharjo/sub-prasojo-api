<?php

declare(strict_types=1);

namespace App\Http\Resources\Ulasan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UlasanItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_review' => $this->id_review,
            'tanggal' => $this->tanggal ? date('Y-m-d', strtotime((string)$this->tanggal)) : null,
            'no_reg' => $this->no_reg,
            'layanan' => $this->layanan,
            'rating' => $this->rating,
            'komentar' => $this->komentar,
        ];
    }
}