<?php

declare(strict_types=1);

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLASampleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $layananNama = data_get($this->resource, 'layanan.layanan_nama');
        $layananKode = data_get($this->resource, 'ajuan_layanan_kode');

        $durasiMenit = (int) (data_get($this->resource, 'durasi_sla_menit') ?? 0);

        $jam = floor($durasiMenit / 60);
        $menit = round($durasiMenit % 60);
        $durasiText = "";
        if ($jam > 0) {
            $durasiText .= $jam . " Jam ";
        }
        $durasiText .= $menit . " Menit";
        $durasiText = trim($durasiText);
        if ($durasiText === "0 Menit" || $durasiText === "") {
            $durasiText = "0 Menit";
        }

        $targetSlaMenit = (int) (data_get($this->resource, 'target_sla_menit') ?? data_get($this->resource, 'target_sla_menit_aktual') ?? 360);
        if ($targetSlaMenit <= 0) {
            $targetSlaMenit = (int) (config('sla.default_jam', 6) * 60);
        }

        $targetJam = floor($targetSlaMenit / 60);
        $targetMenitSisa = $targetSlaMenit % 60;
        $targetSlaText = "";
        if ($targetJam > 0) {
            $targetSlaText .= $targetJam . " Jam ";
        }
        if ($targetMenitSisa > 0) {
            $targetSlaText .= $targetMenitSisa . " Menit";
        }
        $targetSlaText = trim($targetSlaText);
        if (empty($targetSlaText)) {
            $targetSlaText = "6 Jam";
        }

        $isTepatWaktu = $durasiMenit <= $targetSlaMenit;

        $pelaporName = data_get($this->resource, 'pelapor.fullname') ?? data_get($this->resource, 'pelapor.name');
        $pelaporRole = data_get($this->resource, 'ajuan_pelapor_role_name');
        $isOnline = data_get($this->resource, 'ajuan_is_online');
        $pelaporChannel = $isOnline ? 'Online' : 'Offline';

        $waktuMulai = data_get($this->resource, 'waktu_mulai');
        $waktuSelesai = data_get($this->resource, 'waktu_selesai');
        $tanggalDiterima = data_get($this->resource, 'ajuan_create_datetime');

        $formatDateTime = function ($dt) {
            if (!$dt) return null;
            if (is_string($dt)) return date('d-m-Y H:i:s', strtotime($dt));
            if ($dt instanceof \DateTimeInterface) return $dt->format('d-m-Y H:i:s');
            return null;
        };

        return [
            'ajuan_id' => data_get($this->resource, 'ajuan_id'),
            'no_reg' => data_get($this->resource, 'ajuan_no_reg'),
            'layanan_kode' => $layananKode,
            'jenis_layanan' => $layananNama ? strtoupper($layananNama) : ($layananKode ?? '-'),
            'pelapor_role' => $pelaporRole,
            'pelapor_nama' => $pelaporName,
            'pelapor_channel' => $pelaporChannel,
            'pelapor_display' => $pelaporRole ? "{$pelaporRole} ({$pelaporChannel})" : $pelaporChannel,
            'tanggal_diterima' => $formatDateTime($tanggalDiterima),
            'waktu_mulai_proses' => $formatDateTime($waktuMulai),
            'waktu_selesai' => $formatDateTime($waktuSelesai),
            'durasi_penyelesaian_menit' => $durasiMenit,
            'durasi_penyelesaian_text' => $durasiText,
            'target_sla_menit' => $targetSlaMenit,
            'target_sla_text' => $targetSlaText,
            'status_sla' => $isTepatWaktu ? 'Tepat Waktu' : 'Terlambat',
            'is_tepat_waktu' => $isTepatWaktu,
        ];
    }
}
