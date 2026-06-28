<?php

declare(strict_types=1);

namespace App\Http\Requests\Sla;

use Illuminate\Foundation\Http\FormRequest;

class SlaListRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'page.integer' => 'Page harus berupa angka.',
            'page.min' => 'Page minimal 1.',
            'per_page.integer' => 'Per page harus berupa angka.',
            'per_page.min' => 'Per page minimal 1.',
            'per_page.max' => 'Per page maksimal 100.',
        ];
    }
}