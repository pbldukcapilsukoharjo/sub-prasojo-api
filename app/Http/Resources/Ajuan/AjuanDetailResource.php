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
            'tanggal_ajuan' => $this->ajuan_create_datetime ? $this->ajuan_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
            'tanggal_ajuan_parse' => $this->ajuan_create_datetime ? $this->ajuan_create_datetime->format('Y-m-d, H:i') : null,
                
            'kode_layanan' => $this->ajuan_layanan_kode,
            'jenis_ajuan' => $this->jenisAjuan?->ja_judul,
            'jalur' => $this->ajuan_is_online ? 'Online' : 'Offline',
            'pelapor' => $this->ajuan_pelapor_role_name,
            'status' => $this->ajuan_status,
            'tanggal' => $this->ajuan_create_datetime ? $this->ajuan_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
            'tanggal_parse' => $this->ajuan_create_datetime ? $this->ajuan_create_datetime->format('Y-m-d, H:i') : null,

            'timeline' => $this->logStatuses->map(
                fn ($item) => [
                    'id' => $item->log_id,
                    'status' => $item->log_status,
                    'note' => $item->log_note,
                    'tanggal' => $item->log_create_datetime ? $item->log_create_datetime->locale('id')->translatedFormat('d F Y, H:i') : null,
                    'tanggal_parse' => $item->log_create_datetime ? $item->log_create_datetime->format('Y-m-d, H:i') : null,
                ]
            ),
        ];
    }
}