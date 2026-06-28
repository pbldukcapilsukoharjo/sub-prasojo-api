<?php

declare(strict_types=1);

namespace App\Http\Requests\Sla;

use Illuminate\Foundation\Http\FormRequest;

class SlaKpiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'layanan_kode' => ['nullable', 'string', 'exists:layanan,layanan_kode'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'layanan_kode.exists' => 'Kode layanan tidak ditemukan.',
        ];
    }
}