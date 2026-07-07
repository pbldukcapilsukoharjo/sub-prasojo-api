<?php

declare(strict_types=1);

namespace App\Http\Requests\Produk;

use Illuminate\Foundation\Http\FormRequest;

final class IndexProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer'],
            'search' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'kecamatan' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'periode' => ['nullable', 'integer'],
            'sort_by' => ['nullable', 'string'],
            'sort' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'layanan' => ['nullable', 'string'],
            'nama_identitas_produk' => ['nullable', 'string'],
        ];
    }
}