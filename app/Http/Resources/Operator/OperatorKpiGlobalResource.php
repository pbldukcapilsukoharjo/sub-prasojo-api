<?php

declare(strict_types=1);

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OperatorKpiGlobalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'total_ajuan' => $this['total_ajuan'],
            'total_selesai' => $this['total_selesai'],
            'tingkat_selesai' => $this['tingkat_selesai'],
            'rata_rata_durasi' => $this['rata_rata_durasi'],
        ];
    }
}
