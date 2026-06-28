<?php

declare(strict_types=1);

namespace App\Http\Resources\Sla;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SlaKpiResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(
        Request $request,
    ): array {

        return [

            'rata_rata_global_text' =>
                data_get(
                    $this->resource,
                    'rata_rata_global_text',
                    '0 Jam 0 Menit',
                ),

            'capaian_sla_persen' =>
                data_get(
                    $this->resource,
                    'capaian_sla_persen',
                    0,
                ),
        ];
    }
}