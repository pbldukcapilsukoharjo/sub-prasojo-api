<?php

declare(strict_types=1);

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('master_libur_nasionals', 'tanggal')->ignore($id),
            ],
            'keterangan' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal hari libur wajib diisi.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'tanggal.unique' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.',
            'keterangan.required' => 'Keterangan hari libur wajib diisi.',
            'keterangan.max' => 'Keterangan hari libur maksimal 255 karakter.',
        ];
    }
}
