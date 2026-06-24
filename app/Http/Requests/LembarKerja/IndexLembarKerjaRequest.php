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
            'period' => ['nullable', 'string'],
            'sortBy' => ['nullable', 'in:newest,oldest'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'reporter' => ['nullable', 'string'],
        ];
    }
}