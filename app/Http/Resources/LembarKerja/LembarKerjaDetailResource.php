<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LembarKerjaDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->lk_id,

            'no_regis' => $this->lk_ajuan_no_reg,

            'nama' => $this->ajuan?->ajuan_pelapor_role_name,

            'nik' => $this->ajuan?->ajuan_pelapor_nik,

            'jenis_layanan' => $this->lk_layanan_kode,

            'kecamatan' => $this->ajuan?->ajuan_kecamatan_name,
        ];
    }
}