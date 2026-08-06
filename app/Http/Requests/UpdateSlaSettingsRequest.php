<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $validStatuses = [
            'BELUM DIVERIFIKASI',
            'PROSES VERIFIKASI',
            'DISETUJUI',
            'SELESAI DIPROSES',
            'DITOLAK',
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
