<?php

declare(strict_types=1);

namespace App\Http\Requests\Produk;

use Illuminate\Foundation\Http\FormRequest;

final class ShowProdukRequest extends FormRequest
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