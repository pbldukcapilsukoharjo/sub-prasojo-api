<?php

namespace App\Http\Requests\PeringkatOperator;

use Illuminate\Foundation\Http\FormRequest;

class DetailPeringkatOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}