<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string'
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
            'password.required' => 'Password Wajib Diisi'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
