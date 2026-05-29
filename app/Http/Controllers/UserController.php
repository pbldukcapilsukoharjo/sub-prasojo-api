<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
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
            
            return response()->json([
                'code' => 200,
                'message' => 'Data user berhasil diambil',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => 'Gagal mengambil data user',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}