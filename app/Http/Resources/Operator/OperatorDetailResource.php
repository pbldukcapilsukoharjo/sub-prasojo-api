<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'desa' => $this->desa,
            'kecamatan' => $this->kecamatan,
            'total_ajuan' => $this->total_ajuan,
            'total_selesai' => $this->total_selesai,
            'tingkat_selesai' => $this->tingkat_selesai,
            'layanan_perbulan' => $this->layanan_perbulan ?? [],
            'riwayat_layanan' => OperatorHistoryResource::collection($this->riwayat ?? []),
        ];
    }
}