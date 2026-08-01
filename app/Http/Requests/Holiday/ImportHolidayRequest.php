<?php

declare(strict_types=1);

namespace App\Http\Requests\Holiday;

use Illuminate\Foundation\Http\FormRequest;

final class ImportHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib diunggah.',
            'file.file' => 'Unggahan harus berupa file.',
            'file.mimes' => 'Format file harus berupa .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ];
    }
}
