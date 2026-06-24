<?php

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLADetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'jenis_layanan' => $this['jenis_layanan'],
            'jumlah_ajuan' => $this['jumlah_ajuan'],
            'rata_rata_waktu' => $this['rata_rata_waktu'],
        ];
    }
}