<?php

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLAResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Untuk list SLA
        if (isset($this->jenis_layanan)) {
            return [
                'id' => $this->id,
                'jenis_layanan' => $this->jenis_layanan,
                'jumlah_ajuan' => $this->jumlah_ajuan,
                'rata_rata_waktu' => round($this->rata_rata_waktu, 1),
            ];
        }

        // Untuk KPI (summary)
        return [
            'rata_rata_global_text' => $this->formatWaktu($this->rata_rata_global ?? 0),
            'capaian_sla_persen' => round($this->capaian_sla ?? 0, 2),
        ];
    }

    private function formatWaktu($menit): string
    {
        if ($menit < 60) {
            return round($menit) . ' Menit';
        }

        $jam = floor($menit / 60);
        $sisa_menit = round($menit % 60);

        return $sisa_menit > 0
            ? "{$jam} Jam {$sisa_menit} Menit"
            : "{$jam} Jam";
    }
}