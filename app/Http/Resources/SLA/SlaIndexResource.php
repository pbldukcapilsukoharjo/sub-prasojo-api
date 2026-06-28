<?php

declare(strict_types=1);

namespace App\Http\Resources\Sla;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SlaIndexResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(
        Request $request,
    ): array {

        return [

            'rata_rata_waktu_proses' =>
                $this['rata_rata_waktu_proses'],

            'pencapaian_sla' =>
                $this['pencapaian_sla'],

            'target_sla' =>
                $this['target_sla'],

            'daftar_rincian' => [

                'list' =>
                    $this['daftar_rincian']['list'],

                'meta' =>
                    $this['daftar_rincian']['meta'],
            ],
        ];
    }
}