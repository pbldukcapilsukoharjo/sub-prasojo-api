<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper to authenticate requests with PASETO Bearer token.
     *
     * @param \App\Models\Monitoring\SubUser|null $user
     * @return \App\Models\Monitoring\SubUser
     */
    protected function authenticateWithPaseto(?\App\Models\Monitoring\SubUser $user = null): \App\Models\Monitoring\SubUser
    {
        if (!$user) {
            $user = \App\Models\Monitoring\SubUser::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'fullname' => 'Test User',
                'email' => 'test_' . \Illuminate\Support\Str::random(10) . '@example.com',
                'verified_at' => now(),
                'hashed_password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
        }

        $token = resolve(\App\Services\PasetoService::class)->generateAccessToken($user);

        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $user;
    }
}
