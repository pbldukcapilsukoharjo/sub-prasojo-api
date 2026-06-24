<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SLARequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'      => ['nullable', 'integer', 'min:1'],
            'search'    => ['nullable', 'string'],
            'district'  => ['nullable', 'string'],
            'period'    => ['nullable', 'string'],
            'sortBy'    => ['nullable', 'in:newest,oldest'],
            'startDate' => ['nullable', 'date'],
            'endDate'   => ['nullable', 'date'],
        ];
    }
}