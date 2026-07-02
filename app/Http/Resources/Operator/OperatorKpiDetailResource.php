<?php

declare(strict_types=1);

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OperatorKpiDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'nama' => $this['nama'],
            'total_ajuan' => $this['total_ajuan'],
            'total_selesai' => $this['total_selesai'],
            'tingkat_selesai' => $this['tingkat_selesai'],
            'layanan_perbulan' => $this['layanan_perbulan'],
        ];
    }
}
