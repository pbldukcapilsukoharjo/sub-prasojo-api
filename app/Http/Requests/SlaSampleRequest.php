<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlaSampleRequest extends FormRequest
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
        return [
            'kategori' => [
                'nullable',
                'string',
                'in:tercepat,terlambat,terbaru,30_hari',
            ],
            'ajuan_id' => [
                'nullable',
                'integer',
            ],
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'id_kecamatan' => [
                'nullable',
                'string',
            ],
            'id_layanan' => [
                'nullable',
                'string',
            ],
            'operator_id' => [
                'nullable',
                'integer',
            ],
            'pelapor' => [
                'nullable',
                'string',
            ],
            'periode_bulan' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
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
            'sort_by' => [
                'nullable',
                'in:newest,oldest,fastest,slowest',
            ],
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
        ];
    }

    /**
     * Default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->page ?? 1,
            'per_page' => $this->per_page ?? 10,
        ]);
    }
}
