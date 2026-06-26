<?php

namespace App\Http\Requests\PeringkatOperator;

use Illuminate\Foundation\Http\FormRequest;

class PeringkatOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'page' => 'nullable|integer|min:1',

            'search' => 'nullable|string',

            'district' => 'nullable|string',

            'period' => 'nullable|string',

            'sortBy' => 'nullable|in:newest,oldest,highest,lowest',

            'startDate' => 'nullable|date',

            'endDate' => 'nullable|date',

            'operator' => 'nullable|string',

        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([

            'page' => $this->page ?? 1,

            'sortBy' => $this->sortBy ?? 'newest',

        ]);
    }
}