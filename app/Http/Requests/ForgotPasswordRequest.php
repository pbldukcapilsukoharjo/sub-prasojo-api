<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

final class ForgotPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:sub_users,email']
        ];
    }
    
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Format Email Salah',
            'email.required' => 'Email Wajib Diisi',
            'email.exists' => 'Email tidak terdaftar',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Validasi gagal. Silakan periksa kembali input Anda.',
                'data' => $validator->errors()
            ], 400)
        );
    }
}
