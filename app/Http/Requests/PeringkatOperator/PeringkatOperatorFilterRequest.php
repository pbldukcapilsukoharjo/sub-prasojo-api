<?php

declare(strict_types=1);

namespace App\Http\Requests\PeringkatOperator;

use Illuminate\Foundation\Http\FormRequest;

class PeringkatOperatorFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
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

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'search' => [
                'nullable',
                'string',
            ],

            'district' => [
                'nullable',
                'string',
            ],

            'period' => [
                'nullable',
                'in:today,this_week,this_month,this_year',
            ],

            'sortBy' => [
                'nullable',
                'in:newest,oldest',
            ],

            'startDate' => [
                'nullable',
                'date',
            ],

            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
            ],

            'operator' => [
                'nullable',
                'integer',
            ],

        ];
    }
}