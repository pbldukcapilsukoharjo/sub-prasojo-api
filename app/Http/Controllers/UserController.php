<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function getUser(Request $request)
    {
        try {
            $user_id = $request->attributes->get('auth_user_id');
            $user = $this->userService->getUser($user_id);
            
            return ApiResponse::success('Data user berhasil diambil', $user);
        } catch (\Throwable $e) {
            Log::error('[UserController@getUser] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data user', 400, ['error' => $e->getMessage()]);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user_id = $request->attributes->get('auth_user_id');
            $user = $this->userService->updateProfile($user_id, $request->validated());
            
            return ApiResponse::success('Profil berhasil diperbarui', null);
        } catch (\Throwable $e) {
            Log::error('[UserController@updateProfile] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal memperbarui profil', 400, ['error' => $e->getMessage()]);
        }
    }
}