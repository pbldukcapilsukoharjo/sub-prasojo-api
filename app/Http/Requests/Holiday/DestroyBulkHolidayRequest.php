<?php

declare(strict_types=1);

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

final class DestroyBulkHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:master_libur_nasionals,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Daftar ID yang akan dihapus wajib diisi.',
            'ids.array' => 'Daftar ID harus berupa array.',
            'ids.min' => 'Daftar ID minimal berisi 1 item.',
            'ids.*.required' => 'ID hari libur wajib diisi.',
            'ids.*.integer' => 'ID hari libur harus berupa angka.',
            'ids.*.exists' => 'Salah satu ID hari libur tidak ditemukan.',
        ];
    }
}
