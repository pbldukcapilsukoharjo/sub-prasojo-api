<?php

declare(strict_types=1);

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

final class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'holidays' => ['required', 'array', 'min:1'],
            'holidays.*.tanggal' => ['required', 'date_format:Y-m-d', 'distinct'],
            'holidays.*.keterangan' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'holidays.required' => 'Data hari libur wajib diisi.',
            'holidays.array' => 'Data hari libur harus berupa array.',
            'holidays.min' => 'Data hari libur minimal berisi 1 item.',
            'holidays.*.tanggal.required' => 'Tanggal hari libur wajib diisi.',
            'holidays.*.tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'holidays.*.tanggal.distinct' => 'Terdapat tanggal duplikat dalam data yang dikirim.',
            'holidays.*.keterangan.required' => 'Keterangan hari libur wajib diisi.',
            'holidays.*.keterangan.max' => 'Keterangan hari libur maksimal 255 karakter.',
        ];
    }
}
