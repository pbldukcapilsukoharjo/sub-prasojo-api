<?php

declare(strict_types=1);

namespace App\Http\Resources\Ajuan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AjuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ajuan_id,
            'no_regis' => $this->ajuan_no_reg,
            'kode_ajuan' => $this->ajuan_layanan_kode,
            'jenis_ajuan' => $this->jenisAjuan?->ja_judul,
            'jalur' => $this->ajuan_is_online ? 'Online' : 'Offline',
            'pelapor' => $this->ajuan_pelapor_role_name,
            'kecamatan' => $this->ajuan_kecamatan_name,
            'tanggal' => optional($this->ajuan_create_datetime)->format('Y-m-d'),
            'waktu' => optional($this->ajuan_create_datetime)->format('H:i'),
            'status' => $this->ajuan_status,
        ];
    }
}