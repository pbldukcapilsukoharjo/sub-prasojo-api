<?php

namespace App\Http\Requests\Ulasan;

use Illuminate\Foundation\Http\FormRequest;

class UlasanRequest extends FormRequest
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
            'sortBy' => ['nullable', 'in:newest,oldest'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'rating' => ['nullable'],
            'serviceType' => ['nullable'],
        ];
    }
}