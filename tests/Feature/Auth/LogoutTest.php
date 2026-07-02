<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Monitoring\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_logout()
    {
        $this->disableCookieEncryption();
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User',
            'email' => 'logout@example.com',
            'hashed_password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'logout@example.com',
            'password' => 'password123'
        ]);
        
        $token = $loginResponse->json('data.access_token');
        $refreshToken = $loginResponse->getCookie('refresh_token', false)->getValue();

        $response = $this->call('POST', '/api/v1/auth/logout', [], [
            'refresh_token' => $refreshToken
        ], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
            'HTTP_ACCEPT' => 'application/json'
        ]);

        $response->assertStatus(200);
    }
}
