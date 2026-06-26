<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SubUser;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    private PasetoService $pasetoService;

    public function __construct(PasetoService $pasetoService)
    {
        $this->pasetoService = $pasetoService;
    }
    
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

    public function saveToken(object $data)
    {
        try {
            RefreshToken::create([
                'sub_user_id' => $data->get('user_id'),
                'jti' => $data->get('jti'),
                'expired_at' => now()->addDays(7),
                'revoked' => false,
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => ['Terjadi kesalahan saat menyimpan token. Silakan coba lagi. ' . $e->getMessage()],
            ]);
        }
    }

    public function refreshToken(object $data)
    {
        try {
            $jti = $data->get('jti');
            $refreshToken = RefreshToken::where('jti', $jti)->where('revoked', false)->first();
            
            if (!$refreshToken) {
                throw ValidationException::withMessages([
                    'error' => ['Token tidak ditemukan atau sudah di-revoke.'],
                ]);
            }
            
            RefreshToken::where('jti', $jti)->update(['revoked' => true, 'updated_at' => now()]);

            $user = SubUser::find($data->get('user_id'));

            $newAccessToken = $this->pasetoService->generateAccessToken($user);
            $newRefreshToken = $this->pasetoService->generateRefreshToken($user);

            RefreshToken::create([
                'sub_user_id' => $user->id,
                'jti' => $this->pasetoService->parseToken($newRefreshToken)->get('jti'),
                'expired_at' => now()->addDays(7),
            ]);

            return [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ];
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => ['Gagal memperbarui token: ' . $e->getMessage()],
            ]);
        }
    }

    public function logout(object $data, object $bearerToken): bool
    {
        try {
            $jti = $data->get('jti');
            $refreshToken = RefreshToken::where('jti', $jti)->where('revoked', false)->first();
            if (!$refreshToken) {
                return false;
            }
            RefreshToken::where('jti', $jti)->update(['revoked' => true, 'updated_at' => now()]);

            if ($bearerToken) {
                $jti = $bearerToken->get('jti');
                $exp = $bearerToken->get('exp');
                $ttl = now()->diffInSeconds($exp, false);

                if ($ttl > 0) {
                    Cache::put("blacklist:$jti", true, $ttl);
                }
            }

            return true;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => ['Gagal logout: ' . $e->getMessage()],
            ]);
        }
    }
}
