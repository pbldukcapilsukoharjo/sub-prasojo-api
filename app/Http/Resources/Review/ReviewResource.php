<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Identitas review
            'id' => $this->review_id,
            'ajuan_id' => $this->review_ajuan_id,
            'pelapor_id' => $this->review_pelapor_id,
            
            // Data relasi ajuan (jika ada)
            'ajuan_no_reg' => optional($this->ajuan)->ajuan_no_reg,
            'layanan_nama' => optional($this->ajuan)->ajuan_layanan_nama,
            
            // Data relasi pelapor (jika ada)
            'pelapor_nama' => optional($this->pelapor)->name,
            
            // Isi review
            'rating' => $this->review_rating,
            'content' => $this->review_content,
            
            // Waktu dengan format yang konsisten
            'tanggal' => optional($this->review_create_datetime)->format('Y-m-d'),
            'waktu' => optional($this->review_create_datetime)->format('H:i'),
            'created_at' => optional($this->review_create_datetime)->toDateTimeString(),
        ];
    }
}