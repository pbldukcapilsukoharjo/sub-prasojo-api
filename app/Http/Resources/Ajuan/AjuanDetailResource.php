<?php

declare(strict_types=1);

namespace App\Http\Resources\Ajuan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AjuanDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ajuan_id,
            'no_regis' => $this->ajuan_no_reg,
            'nama' => $this->pelapor?->fullname,
            'nik' => $this->ajuan_pelapor_nik,
            'jenis_layanan' => $this->ajuan_layanan_kode,
            'kecamatan' => $this->ajuan_kecamatan_name,
            'tanggal_ajuan' => optional($this->ajuan_create_datetime)
                ->format('Y-m-d, H:i'),

            'timeline' => $this->logStatuses->map(
                fn ($item) => [
                    'id' => $item->log_id,
                    'status' => $item->log_status,
                    'note' => $item->log_note,
                    'tanggal' => optional($item->log_create_datetime)
                        ->format('Y-m-d'),
                    'waktu' => optional($item->log_create_datetime)
                        ->format('H:i'),
                ]
            ),
        ];
    }
}