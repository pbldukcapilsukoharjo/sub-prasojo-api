<?php

declare(strict_types=1);

namespace App\Http\Requests\Ulasan;

use Illuminate\Foundation\Http\FormRequest;

final class UlasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'in:newest,oldest,rating_asc,rating_desc'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'layanan_kode' => ['nullable', 'string'],
            'reporter' => ['nullable', 'string'],
            'pelapor' => ['nullable', 'string'],
            'id_pelapor' => ['nullable', 'string'],
        ];
    }
}