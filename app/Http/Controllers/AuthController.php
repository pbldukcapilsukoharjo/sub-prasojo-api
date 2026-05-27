<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Services\PasetoService;
use Illuminate\Http\Request;

class AuthController extends Controller
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

            return response()->json([
                'code' => 201,
                'message' => 'Registrasi berhasil',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => 'Registrasi gagal',
                'error' => $e->getMessage()
            ], 400);
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
            
            return response()->json([
                'code' => 200,
                'message' => 'Login berhasil',
                'data' => [
                    'access_token' => $access_token
                ]
            ], 200)->cookie('refresh_token', $refresh_token, 60 * 24 * 7, null, null, true, true);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Login gagal',
                'error' => $e->getMessage()
            ], 401);
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
    
            return response()->json([
                'code' => 200,
                'message' => 'Logout berhasil'
            ], 200)->withoutCookie('refresh_token');
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Logout gagal',
                'error' => $e->getMessage()
            ], 401);
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

            return response()->json([
                'code' => 200,
                'message' => 'Refresh berhasil',
                'data' => [
                    'access_token' => $result['access_token']
                ]
            ], 200)->cookie('refresh_token', $result['refresh_token'], 60 * 24 * 7, null, null, true, true);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Refresh gagal',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
