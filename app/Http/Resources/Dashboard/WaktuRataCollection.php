<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WaktuRataCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rata_rata_waktu_proses' => $this['rata_rata_waktu_proses'],

            'pencapaian_sla' => $this['pencapaian_sla'],

            'target_sla' => $this['target_sla'],

            'tabel' => [
                'layanan' => $this['tabel']['layanan'],

                'meta' => $this['tabel']['meta'],
            ]
        ];
    }
}