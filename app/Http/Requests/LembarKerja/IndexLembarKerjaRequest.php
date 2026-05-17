<?php

declare(strict_types=1);

namespace App\Http\Requests\LembarKerja;

use Illuminate\Foundation\Http\FormRequest;

final class IndexLembarKerjaRequest extends FormRequest
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
            'status' => ['nullable', 'string'],

            'layanan_kode' => ['nullable', 'string'],

            'is_online' => ['nullable', 'boolean'],

            'is_mandiri' => ['nullable', 'boolean'],

            'is_produk' => ['nullable', 'boolean'],

            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}