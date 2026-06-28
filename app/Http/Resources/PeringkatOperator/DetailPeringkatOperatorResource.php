<?php

namespace App\Http\Resources\PeringkatOperator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPeringkatOperatorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                $this['id'],

            'nama' =>
                $this['nama'],

            'total_ajuan' =>
                $this['total_ajuan'],

            'total_selesai' =>
                $this['total_selesai'],

            'tingkat_selesai' =>
                $this['tingkat_selesai'],

            'layanan_perbulan' =>
                $this['layanan_perbulan'],

            'riwayat_layanan' =>
                collect(
                    $this['riwayat_layanan']
                )->map(function ($item) {

                    return [

                        'id' =>
                            $item['id'],

                        'no_regis' =>
                            $item['no_regis'],

                        'pemohon' =>
                            $item['pemohon'],

                        'kode_ajuan' =>
                            $item['kode_ajuan'],

                        'desa' =>
                            $item['desa'],

                        'tanggal' =>
                            $item['tanggal'],

                        'waktu' =>
                            $item['waktu'],

                        'status' =>
                            $item['status'],
                    ];
                })->values(),
        ];
    }
}