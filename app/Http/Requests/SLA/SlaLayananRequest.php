<?php

declare(strict_types=1);

namespace App\Http\Requests\Sla;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SlaLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'layanan_kode' => [
                'nullable',
                'string',
                'max:20',
            ],

            'operator_id' => [
                'nullable',
                'integer',
            ],

            'status_sla' => [
                'nullable',
                Rule::in([
                    'TEPAT_WAKTU',
                    'TERLAMBAT',
                    'BERJALAN',
                ]),
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'status_sla.in' =>
                'Status SLA tidak valid.',

            'end_date.after_or_equal' =>
                'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.',
        ];
    }
}