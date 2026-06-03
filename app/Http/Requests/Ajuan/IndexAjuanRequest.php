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
            'sortBy' => ['nullable', 'string'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'reporter' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}