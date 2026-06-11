<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PeringkatOperatorCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_layanan' => $this['total_layanan'],

            'rata_rata_durasi' => $this['rata_rata_durasi'],

            'persentase_sla' => $this['persentase_sla'],

            'tabel' => [
                'operator' => $this['tabel']['operator'],

                'meta' => $this['tabel']['meta'],
            ]
        ];
    }
}