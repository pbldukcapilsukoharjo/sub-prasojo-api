<?php

namespace App\Services;

use App\Models\SubUser;
use Illuminate\Validation\ValidationException;
class UserService 
{   
    public function getUser(string $userId) 
    {
        $user = SubUser::select(['fullname', 'email'])->find($userId);
        if (!$user) {
            throw ValidationException::withMessages([
                'error' => ['User tidak ditemukan'],
            ]);
        }
        return $user;
    }
}