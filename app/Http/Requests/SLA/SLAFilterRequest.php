<?php

declare(strict_types=1);

namespace App\Http\Requests\Sla;

use Illuminate\Foundation\Http\FormRequest;

final class SlaKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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

            'kecamatan_code' => [
                'nullable',
                'string',
                'max:20',
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' =>
                'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.',
        ];
    }
}