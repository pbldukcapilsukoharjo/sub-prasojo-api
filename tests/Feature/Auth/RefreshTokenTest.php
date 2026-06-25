<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_refresh_token()
    {
        $this->disableCookieEncryption();
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User',
            'email' => 'refresh@example.com',
            'hashed_password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'refresh@example.com',
            'password' => 'password123'
        ]);
        
        $token = $loginResponse->json('data.access_token');
        $refreshToken = $loginResponse->getCookie('refresh_token', false)->getValue();

        $response = $this->call('POST', '/api/v1/auth/refresh', [], [
            'refresh_token' => $refreshToken
        ], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
            'HTTP_ACCEPT' => 'application/json'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => ['access_token']
                 ]);
    }
}
