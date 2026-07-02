<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

final class OperatorFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
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
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'search' => [
                'nullable',
                'string',
            ],
            'id_kecamatan' => [
                'nullable',
            ],
            'periode_bulan' => [
                'nullable',
                'integer',
                'between:1,12',
            ],
            'sort' => [
                'nullable',
                'string',
                'in:newest,oldest',
            ],
            'start_date' => [
                'nullable',
                'string', // Bisa berupa string format dd-mm-yyyy
            ],
            'end_date' => [
                'nullable',
                'string',
            ],
            'id_operator' => [
                'nullable',
                'integer',
            ],
            'tahun' => [
                'nullable',
                'integer',
            ],
            'id_layanan' => [
                'nullable',
            ],
        ];
    }
}
