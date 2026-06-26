<?php

namespace App\Http\Resources\PeringkatOperator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeringkatOperatorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'total_layanan' => $this['total_layanan'] ?? 0,

            'rata_rata_durasi' => $this['rata_rata_durasi'] ?? 0,

            // UI menampilkan persentase tingkat layanan selesai
            'tingkat_layanan' => $this['tingkat_layanan'] ?? 0,

            'peringkat_operator' => [

                'list' => collect($this['list'] ?? [])->map(function ($item) {

                    return [

                        'id' => $item['id'],

                        'peringkat' => $item['peringkat'],

                        'operator' => $item['operator'],

                        'desa' => $item['desa'],

                        'kecamatan' => $item['kecamatan'],

                        'jumlah_ajuan' => $item['jumlah_ajuan'],

                    ];

                }),

                'meta' => [

                    'page' => $this['meta']['page'] ?? 1,

                    'per_page' => $this['meta']['per_page'] ?? 5,

                    'total' => $this['meta']['total'] ?? 0,

                    'total_page' => $this['meta']['total_page'] ?? 1,

                ]
            ]
        ];
    }
}