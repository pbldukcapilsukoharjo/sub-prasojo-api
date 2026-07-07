<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Monitoring\SubUser;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final class UserService 
{   
    public function getUser(string $userId) 
    {
        try {
            $user = SubUser::select(['fullname', 'email'])->find($userId);
            if (!$user) {
                throw ValidationException::withMessages([
                    'error' => ['User tidak ditemukan'],
                ]);
            }
            return $user;
        } catch (\Throwable $e) {
            Log::error('[UserService@getUser] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function updateProfile(string $userId, array $data)
    {
        try {
            $user = SubUser::find($userId);
            if (!$user) {
                throw ValidationException::withMessages([
                    'error' => ['User tidak ditemukan'],
                ]);
            }

            if (isset($data['fullname'])) {
                $user->fullname = $data['fullname'];
            }

            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            if (isset($data['password'])) {
                $user->hashed_password = Hash::make($data['password']);
            }

            $user->save();

            return $this->getUser($userId);
        } catch (\Throwable $e) {
            Log::error('[UserService@updateProfile] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}