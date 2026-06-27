<?php

declare(strict_types=1);

namespace App\Http\Requests\SLA;

use Illuminate\Foundation\Http\FormRequest;

class SLAFilterRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Filter
            |--------------------------------------------------------------------------
            */
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'period' => [
                'nullable',
                'in:today,this_week,this_month,this_year',
            ],

            /*
            |--------------------------------------------------------------------------
            | Date Range
            |--------------------------------------------------------------------------
            */
            'startDate' => [
                'nullable',
                'date',
            ],

            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */
            'sortBy' => [
                'nullable',
                'in:newest,oldest',
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

            'per_page' => $this->per_page ?? 5,

            'sortBy' => $this->sortBy ?? 'newest',
        ]);
    }
}