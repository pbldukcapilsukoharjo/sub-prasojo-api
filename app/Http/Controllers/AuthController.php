<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use App\Services\PasetoService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AuthController extends Controller
{
    protected AuthService $authService;
    protected PasetoService $pasetoService;

    public function __construct(AuthService $authService, PasetoService $pasetoService)
    {
        $this->authService = $authService;
        $this->pasetoService = $pasetoService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());
            $user = $result['user'];

            return ApiResponse::success('Registrasi berhasil', $user, 201);
        } catch (\Throwable $e) {
            Log::error('[AuthController@register] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Registrasi gagal', 400, ['error' => $e->getMessage()]);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());
            $user = $result['user'];

            $access_token = $this->pasetoService->generateAccessToken($user);
            $refresh_token = $this->pasetoService->generateRefreshToken($user);

            $parsed = $this->pasetoService->parseToken($refresh_token);

            $this->authService->saveToken($parsed);

            $options = $this->cookieOptions();

            return ApiResponse::success('Login berhasil', [
                'access_token' => $access_token
            ])->cookie('refresh_token', $refresh_token, 60 * 24 * 7, '/', null, $options['secure'], true, false, $options['sameSite']);
        } catch (\Throwable $e) {
            Log::error('[AuthController@login] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Login gagal', 400, ['error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $access_token = $request->bearerToken();
            $token = $request->cookie('refresh_token');

            if (!$access_token || !$token) {
                throw new \Exception('Token tidak ditemukan');
            }

            $parsed = $this->pasetoService->parseToken($token);
            $access_parsed = $this->pasetoService->parseToken($access_token);

            $result = $this->authService->logout($parsed, $access_parsed);

            if(!$result) {
                throw new \Exception('Terjadi kesalahan pada token');
            }

            return ApiResponse::success('Logout berhasil')->withoutCookie('refresh_token');
        } catch (\Throwable $e) {
            Log::error('[AuthController@logout] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Logout gagal', 400, ['error' => $e->getMessage()]);
        }

    }

    public function refresh(Request $request)
    {
        try {
            $token = $request->cookie('refresh_token');

            if (!$token) {
                throw new \Exception('Refresh token tidak ditemukan');
            }

            $parsed = $this->pasetoService->parseToken($token);

            if ($parsed->get('type') !== 'refresh') {
                throw new \Exception('Invalid token type');
            }

            $result = $this->authService->refreshToken($parsed);

            $options = $this->cookieOptions();

            return ApiResponse::success('Refresh berhasil', [
                'access_token' => $result['access_token']
            ])->cookie('refresh_token', $result['refresh_token'], 60 * 24 * 7, '/', null, $options['secure'], true, false, $options['sameSite']);
        } catch (\Throwable $e) {
            Log::error('[AuthController@refresh] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Refresh gagal', 400, ['error' => $e->getMessage()]);
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $frontendUrl = config('app.frontend_url');

        try {
            $hasValidSignature = $request->hasValidSignature();
            $this->authService->verifyEmail($id, $hash, $hasValidSignature);

            return redirect()->away($frontendUrl . '/verify-success');
        } catch (\Throwable $e) {
            Log::error('[AuthController@verifyEmail] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->away($frontendUrl . '/verify-failed?message=' . urlencode($e->getMessage()));
        }
    }

    public function resendVerification(ResendRequest $request)
    {
        try {
            $this->authService->resendVerification($request->validated());

            return ApiResponse::success('Email verifikasi berhasil dikirim ulang', [], 200);
        } catch (\Throwable $e) {
            Log::error('[AuthController@resendVerification] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengirim ulang email verifikasi', 400, ['error' => $e->getMessage()]);
        }
    }

    public function verificationNotice()
    {
        return ApiResponse::error('Harap verifikasi email Anda terlebih dahulu.', 403);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $this->authService->forgotPassword($request->validated());

            return ApiResponse::success('Email reset password berhasil dikirim', [], 200);
        } catch (\Throwable $e) {
            Log::error('[AuthController@forgotPassword] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengirim email reset password', 400, ['error' => $e->getMessage()]);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $this->authService->resetPassword($request->validated());

            return ApiResponse::success('Password berhasil direset', [], 200);
        } catch (\Throwable $e) {
            Log::error('[AuthController@resetPassword] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mereset password', 400, ['error' => $e->getMessage()]);
        }
    }

    protected function cookieOptions(): array
    {
        $isProduction = config('app.env') === 'production';

        return [
            'secure' => $isProduction,
            'sameSite' => $isProduction ? 'None' : 'Lax',
        ];
    }
}

