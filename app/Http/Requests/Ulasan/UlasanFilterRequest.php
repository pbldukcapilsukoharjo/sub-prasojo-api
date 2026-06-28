<?php

declare(strict_types=1);

namespace App\Http\Requests\Ulasan;

use Illuminate\Foundation\Http\FormRequest;

final class UlasanFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
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

    protected function prepareForValidation(): void
    {
        $this->merge([

            'page' => $this->page ?? 1,

            'sortBy' => $this->sortBy ?? 'newest',

            'rating' => $this->rating ?? 'all',

            'serviceType' => $this->serviceType ?? 'all',

        ]);
    }
}