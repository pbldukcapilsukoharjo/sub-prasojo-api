<?php

declare(strict_types=1);

namespace App\Http\Requests\Pengajuan;

use Illuminate\Foundation\Http\FormRequest;

final class ExportPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_kategori' => ['required', 'string', 'in:lembar_kerja,produk,all'],
            'id_kecamatan' => ['nullable'],
            'id_layanan' => ['nullable'],
            'status' => ['nullable', 'string'],
            'pelapor' => ['nullable', 'string'],
            'search_no_reg' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string'],
            'end_date' => ['nullable', 'string'],
        ];
    }
}
