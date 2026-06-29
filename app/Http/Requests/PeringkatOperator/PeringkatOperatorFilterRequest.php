<?php

declare(strict_types=1);

namespace App\Http\Requests\PeringkatOperator;

use Illuminate\Foundation\Http\FormRequest;

final class PeringkatOperatorFilterRequest extends FormRequest
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

            'sort_by' => [
                'nullable',
                'in:newest,oldest',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'operator' => [
                'nullable',
                'integer',
            ],

        ];
    }
}