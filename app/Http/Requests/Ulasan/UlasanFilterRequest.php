<?php

declare(strict_types=1);

namespace App\Http\Requests\Ulasan;

use Illuminate\Foundation\Http\FormRequest;

class UlasanFilterRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'rating' => [
                'nullable',
                'in:1,2,3,4,5,all',
            ],

            'serviceType' => [
                'nullable',
                'string',
                'max:20',
            ],

            'startDate' => [
                'nullable',
                'date',
                'required_with:endDate',
            ],

            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
                'required_with:startDate',
            ],

            'sortBy' => [
                'nullable',
                'in:newest,oldest',
            ],
        ];
    }

    /**
     * Default values when parameter is not sent.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->page ?? 1,
            'per_page' => $this->per_page ?? 10,
            'sortBy' => $this->sortBy ?? 'newest',
        ]);
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Parameter page harus berupa angka.',
            'page.min' => 'Parameter page minimal 1.',

            'per_page.integer' => 'Parameter per_page harus berupa angka.',
            'per_page.min' => 'Parameter per_page minimal 1.',
            'per_page.max' => 'Parameter per_page maksimal 100.',

            'rating.in' => 'Rating harus bernilai 1 sampai 5 atau all.',

            'startDate.date' => 'Format startDate tidak valid.',
            'endDate.date' => 'Format endDate tidak valid.',
            'endDate.after_or_equal' => 'endDate harus lebih besar atau sama dengan startDate.',

            'sortBy.in' => 'sortBy hanya boleh newest atau oldest.',
        ];
    }
}