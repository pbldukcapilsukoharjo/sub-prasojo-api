<?php

declare(strict_types=1);

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLADetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                $this['id'],

            'jenis_layanan' =>
                $this['jenis_layanan'],

            'jumlah_ajuan' =>
                (int) $this['jumlah_ajuan'],

            'rata_rata_waktu' =>
                (float) $this['rata_rata_waktu'],
        ];
    }
}