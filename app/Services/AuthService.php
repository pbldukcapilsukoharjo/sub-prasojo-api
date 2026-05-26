<?php

namespace App\Services;

use App\Models\SubUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $validatedData)
    {
        try {
            $user = SubUser::create([
                'fullname' => $validatedData['fullname'],
                'email' => $validatedData['email'],
                'hashed_password' => Hash::make($validatedData['password']),
            ]);

            return ['user' => $user];
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => ['Terjadi kesalahan saat pendaftaran. Silakan coba lagi.'],
            ]);
        }
    }

    public function login(array $validatedData)
    {
        $user = SubUser::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->hashed_password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        return ['user' => $user];
    }
}
