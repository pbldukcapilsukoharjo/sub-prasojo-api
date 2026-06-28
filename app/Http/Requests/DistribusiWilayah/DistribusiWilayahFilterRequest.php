<?php

declare(strict_types=1);

namespace App\Http\Requests\DistribusiWilayah;

use Illuminate\Foundation\Http\FormRequest;

class DistribusiWilayahFilterRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
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

            /*
             * Tidak ada di dokumentasi, tetapi diperlukan
             * untuk pagination backend.
             */
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

            /*
             * Nama kecamatan.
             */
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
             * Contoh:
             * today
             * week
             * month
             * year
             * all
             */
            'period' => [
                'nullable',
                'in:today,week,month,year,all',
            ],

            'sortBy' => [
                'nullable',
                'in:newest,oldest,most_submission,least_submission',
            ],

            'startDate' => [
                'nullable',
                'date',
                'required_with:endDate',
            ],

            'endDate' => [
                'nullable',
                'date',
                'required_with:startDate',
                'after_or_equal:startDate',
            ],
        ];
    }

    /**
     * Default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'page' => $this->page ?? 1,

            'per_page' => $this->per_page ?? 10,

            'sortBy' => $this->sortBy ?? 'newest',

            'period' => $this->period ?? 'all',
        ]);
    }

    /**
     * Validation messages.
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

            'district.max' => 'Nama kecamatan maksimal 100 karakter.',

            'period.in' => 'Period tidak valid.',

            'sortBy.in' => 'sortBy tidak valid.',

            'startDate.date' => 'Format startDate tidak valid.',

            'endDate.date' => 'Format endDate tidak valid.',

            'endDate.after_or_equal' => 'endDate harus lebih besar atau sama dengan startDate.',
        ];
    }
}