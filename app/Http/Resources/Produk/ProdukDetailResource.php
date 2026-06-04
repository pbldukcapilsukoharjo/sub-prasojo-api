<?php

declare(strict_types=1);

namespace App\Http\Resources\Produk;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProdukDetailResource extends JsonResource
{
     public function toArray(Request $request): array
    {
        return [
            'id' => $this->prod_id,
            'no_regis' => $this->prod_ajuan_no_reg,
            'nama' => $this->pelapor?->fullname,
            'nik' => $this->pelapor?->nik,
            'jenis_layanan' => $this->ajuan?->ajuan_layanan_kode,
            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,
            'tanggal_ajuan' => optional($this->prod_create_datetime)->format('Y-m-d, H:i'),
            'timeline' => $this->whenLoaded('logStatuses', function () {
                return $this->logStatuses->map(function ($log) {
                    return [
                        'id' => $log->log_id,
                        'status' => $log->log_status,
                        'note' => $log->log_note,
                        'tanggal' => optional($log->log_create_datetime)->format('Y-m-d'),
                        'waktu' => optional($log->log_create_datetime)->format('H:i'),
                    ];
                });
            }),
        ];
    }
}