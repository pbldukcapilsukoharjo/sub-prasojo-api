<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_regis' => $this->no_regis,
            'pemohon' => $this->pemohon,
            'kode_ajuan' => $this->kode_ajuan,
            'desa' => $this->desa,
            'tanggal' => $this->tanggal?->format('d-m-Y'),
            'waktu' => $this->waktu?->format('H:i'),
            'status' => $this->status,
        ];
    }
}