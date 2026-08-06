<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAjuanSlaTargetRequest extends FormRequest
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
            'target_sla_value' => [
                'required',
                'integer',
                'min:1',
            ],
            'target_sla_unit' => [
                'required',
                'string',
                'in:menit,jam,hari',
            ],
        ];
    }
}
