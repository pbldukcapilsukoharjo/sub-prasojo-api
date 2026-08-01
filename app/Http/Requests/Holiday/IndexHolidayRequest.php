<?php

declare(strict_types=1);

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

final class IndexHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
