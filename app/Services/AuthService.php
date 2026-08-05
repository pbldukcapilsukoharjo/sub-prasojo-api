<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Monitoring\SubUser;
use App\Models\Monitoring\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

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

            event(new \Illuminate\Auth\Events\Registered($user));

            \Illuminate\Support\Facades\RateLimiter::hit('resend-verification:' . $user->email, 180);

            return ['user' => $user];
        } catch (\Throwable $e) {
            Log::error('[AuthService@register] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw ValidationException::withMessages([
                'error' => ['Terjadi kesalahan saat pendaftaran. Silakan coba lagi.'],
            ]);
        }
    }

    public function login(array $validatedData)
    {
        try {
            $user = SubUser::where('email', $validatedData['email'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->hashed_password)) {
                throw ValidationException::withMessages([
                    'email' => ['Email atau password salah.'],
                ]);
            }

            return ['user' => $user];
        } catch (\Throwable $e) {
            Log::error('[AuthService@login] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function saveToken(object $data)
    {
        try {
            RefreshToken::create([
                'sub_user_id' => $data->get('user_id'),
                'jti' => $data->get('jti'),
                'expires_at' => now()->addDays(7),
                'revoked' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('[AuthService@saveToken] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
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
                'expires_at' => now()->addDays(7),
            ]);

            return [
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ];
        } catch (\Throwable $e) {
            Log::error('[AuthService@refreshToken] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            if ($e instanceof ValidationException) {
                throw $e;
            }
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
        } catch (\Throwable $e) {
            Log::error('[AuthService@logout] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw ValidationException::withMessages([
                'error' => ['Gagal logout: ' . $e->getMessage()],
            ]);
        }
    }

    public function verifyEmail(string $id, string $hash, bool $hasValidSignature)
    {
        try {
            $user = SubUser::findOrFail($id);

            if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                throw new \Exception('Invalid hash');
            }

            if (!$hasValidSignature) {
                throw new \Exception('Invalid or expired url signature');
            }

            if ($user->hasVerifiedEmail()) {
                throw new \Exception('Email sudah diverifikasi sebelumnya.');
            }

            if ($user->markEmailAsVerified()) {
                event(new \Illuminate\Auth\Events\Verified($user));
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[AuthService@verifyEmail] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function resendVerification(array $validatedData)
    {
        try {
            $email = $validatedData['email'];

            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('resend-verification:' . $email, 1)) {
                $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('resend-verification:' . $email);
                throw new \Exception('Harap tunggu ' . $seconds . ' detik sebelum meminta email verifikasi lagi.');
            }

            $user = SubUser::where('email', $email)->first();
            if (!$user) {
                throw new \Exception('Pengguna tidak ditemukan.');
            }

            if ($user->hasVerifiedEmail()) {
                throw new \Exception('Email sudah diverifikasi sebelumnya.');
            }

            $user->sendEmailVerificationNotification();

            \Illuminate\Support\Facades\RateLimiter::hit('resend-verification:' . $email, 180);

            return true;
        } catch (\Throwable $e) {
            Log::error('[AuthService@resendVerification] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function forgotPassword(array $validatedData)
    {
        try {
            $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
                ['email' => $validatedData['email']]
            );

            if ($status !== \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
                throw new \Exception(__($status));
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[AuthService@forgotPassword] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function resetPassword(array $validatedData)
    {
        try {
            $status = \Illuminate\Support\Facades\Password::broker()->reset(
                $validatedData,
                function ($user, $password) {
                    $user->forceFill([
                        'hashed_password' => Hash::make($password)
                    ])->save();

                    event(new \Illuminate\Auth\Events\PasswordReset($user));
                }
            );

            if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
                throw new \Exception(__($status));
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[AuthService@resetPassword] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
