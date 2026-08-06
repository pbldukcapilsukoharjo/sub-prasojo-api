<?php

declare(strict_types=1);

namespace App\Http\Requests\Ajuan;

use Illuminate\Foundation\Http\FormRequest;

final class IndexAjuanRequest extends FormRequest
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
            'id_kecamatan' => ['nullable', 'string'],
            'period' => ['nullable', 'string'],
            'periode' => ['nullable', 'integer'],
            'sort_by' => ['nullable', 'string'],
            'sort' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'reporter' => ['nullable', 'string'],
            'pelapor' => ['nullable', 'string'],
            'id_pelapor' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'layanan' => ['nullable', 'string'],
            'id_layanan' => ['nullable', 'string'],
            'jenis_ajuan' => ['nullable'],
            'jalur' => ['nullable'],
        ];
    }
}