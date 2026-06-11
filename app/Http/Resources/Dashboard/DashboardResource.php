<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_pengajuan' => $this['total_pengajuan'],
            'total_selesai' => $this['total_selesai'],
            'total_ditolak' => $this['total_ditolak'],
            'label_tamat' => $this['label_tamat'],

            'ajuan_bulanan' => $this['ajuan_bulanan'],

            'peringkat_operator' => $this['peringkat_operator'],

            'distribusi_wilayah' => $this['distribusi_wilayah'],

            'ulasan_pengguna' => $this['ulasan_pengguna'],

            'kepatuhan_sla' => $this['kepatuhan_sla'],

            'total_produk' => $this['total_produk'],

            'rata_proses_selesai' => $this['rata_proses_selesai'],

            'ringkasan_hari_ini' => $this['ringkasan_hari_ini'],

            'status_proses' => $this['status_proses'],

            'tanggal_diambil' => $this['tanggal_diambil'],
        ];
    }
}