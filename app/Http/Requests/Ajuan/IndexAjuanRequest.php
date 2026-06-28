<?php

declare(strict_types=1);

namespace App\Http\Requests\Ajuan;

use Illuminate\Foundation\Http\FormRequest;

final class IndexAjuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'reporter' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}