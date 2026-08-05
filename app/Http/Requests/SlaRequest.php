<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlaRequest extends FormRequest
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
            'id_kecamatan' => [
                'nullable',
                'string',
            ],

            'id_layanan' => [
                'nullable',
                'integer',
            ],

            'operator_id' => [
                'nullable',
                'integer',
            ],

            'periode_bulan' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],

            'pelapor' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Date Range
            |--------------------------------------------------------------------------
            */
            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */
            'sort_by' => [
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
            'sort_by' => $this->sort_by ?? 'newest',
        ]);
    }
}