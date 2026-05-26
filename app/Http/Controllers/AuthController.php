<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Services\PasetoService;

class AuthController extends Controller
{
    protected $authService;
    protected $pasetoService;

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

            $token = $this->pasetoService->generateToken($user);

            return response()->json([
                'code' => 200,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Login gagal',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
