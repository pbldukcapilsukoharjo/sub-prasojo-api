<?php

declare(strict_types=1);

namespace App\Http\Resources\Sla;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SlaLayananResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ajuan_id' =>
                $this['ajuan_id'],

            'no_reg' =>
                $this['no_reg'],

            'layanan_kode' =>
                $this['layanan_kode'],

            'layanan_nama' =>
                $this['layanan_nama'],

            'operator_id' =>
                $this['operator_id'],

            'operator_nama' =>
                $this['operator_nama'],

            'mulai_datetime' =>
                $this['mulai_datetime'],

            'selesai_datetime' =>
                $this['selesai_datetime'],

            'durasi_jam' =>
                $this['durasi_jam'],

            'target_sla_jam' =>
                $this['target_sla_jam'],

            'status_sla' =>
                $this['status_sla'],

            'status_ajuan' =>
                $this['status_ajuan'],
        ];
    }
}