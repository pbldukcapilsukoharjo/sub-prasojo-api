<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'peringkat' => $this->peringkat ?? null,
            'operator' => $this->nama,
            'desa' => $this->desa,
            'kecamatan' => $this->kecamatan,
            'jumlah_ajuan' => $this->total_ajuan,
            'tingkat_selesai' => $this->tingkat_selesai ?? null,
        ];
    }
}