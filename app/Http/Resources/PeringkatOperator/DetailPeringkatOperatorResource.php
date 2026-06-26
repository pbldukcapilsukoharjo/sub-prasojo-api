<?php

namespace App\Http\Resources\PeringkatOperator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPeringkatOperatorResource extends JsonResource
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

            'layanan_perbulan' => [

                'Jan' => $this['layanan_perbulan']['Jan'] ?? 0,
                'Feb' => $this['layanan_perbulan']['Feb'] ?? 0,
                'Mar' => $this['layanan_perbulan']['Mar'] ?? 0,
                'Apr' => $this['layanan_perbulan']['Apr'] ?? 0,
                'Mei' => $this['layanan_perbulan']['Mei'] ?? 0,
                'Jun' => $this['layanan_perbulan']['Jun'] ?? 0,
                'Jul' => $this['layanan_perbulan']['Jul'] ?? 0,
                'Agu' => $this['layanan_perbulan']['Agu'] ?? 0,
                'Sep' => $this['layanan_perbulan']['Sep'] ?? 0,
                'Okt' => $this['layanan_perbulan']['Okt'] ?? 0,
                'Nov' => $this['layanan_perbulan']['Nov'] ?? 0,
                'Des' => $this['layanan_perbulan']['Des'] ?? 0,

            ],

            'riwayat_layanan' => collect($this['riwayat_layanan'] ?? [])
                ->map(function ($item) {

                    return [

                        'id' => $item['id'],

                        'no_regis' => $item['no_regis'],

                        'pemohon' => $item['pemohon'],

                        'kode_ajuan' => $item['kode_ajuan'],

                        'desa' => $item['desa'],

                        'tanggal' => $item['tanggal'],

                        'waktu' => $item['waktu'],

                        'status' => $item['status'],

                    ];

                })->values()

        ];
    }
}