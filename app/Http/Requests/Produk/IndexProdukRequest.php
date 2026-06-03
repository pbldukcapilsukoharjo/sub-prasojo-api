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
            'period' => ['nullable', 'string'],
            'sortBy' => ['nullable', 'string'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
        ];
    }
}