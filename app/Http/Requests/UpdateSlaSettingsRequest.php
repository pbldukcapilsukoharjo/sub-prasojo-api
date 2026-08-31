<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AjuanStatus;

class UpdateSlaSettingsRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        // Semua status dari AjuanStatus enum
        $validStatuses = [
            AjuanStatus::DIAJUKAN,
            AjuanStatus::BELUM_DIVERIFIKASI,
            AjuanStatus::DIVERIFIKASI,
            AjuanStatus::DIPROSES,
            AjuanStatus::MENUNGGU_KONFIRMASI,
            AjuanStatus::DISETUJUI,
            AjuanStatus::DITOLAK,
            AjuanStatus::SELESAI,
            AjuanStatus::SELESAI_DIPROSES,
            AjuanStatus::DIAJUKAN_TTE,
            AjuanStatus::TIDAK_DIPROSES,
            AjuanStatus::SIAP_DOWNLOAD,
            AjuanStatus::SIAP_DICETAK,
            AjuanStatus::SUDAH_DICETAK,
            AjuanStatus::SIAP_DIAMBIL,
            // Status legacy di database yang belum ada di enum
            'PROSES VERIFIKASI',
            'DIKOREKSI',
            'DISETUJUI TANPA NIK',
            'PROSES INPUT NIK',
            'DISETUJUI TERBIT NIK',
        ];

        $validStartStatuses = array_merge(['[FIRST_LOG]'], $validStatuses);

        return [
            'sla_start_status' => [
                'nullable',
                'string',
                'in:' . implode(',', $validStartStatuses),
            ],
            'sla_end_status' => [
                'nullable',
                'string',
                'in:' . implode(',', $validStatuses),
            ],
        ];
    }
}
