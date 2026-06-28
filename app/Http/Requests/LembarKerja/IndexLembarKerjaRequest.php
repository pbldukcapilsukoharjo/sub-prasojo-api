<?php

declare(strict_types=1);

namespace App\Http\Requests\LembarKerja;

use Illuminate\Foundation\Http\FormRequest;

final class IndexLembarKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'kecamatan' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'periode' => ['nullable', 'integer'],
            'sort_by' => ['nullable', 'in:newest,oldest'],
            'sort' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'reporter' => ['nullable', 'string'],
            'pelapor' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'layanan' => ['nullable', 'string'],
        ];
    }
}