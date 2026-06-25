<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SubUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login_with_valid_credentials()
    {
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User',
            'email' => 'test@example.com',
            'hashed_password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => ['access_token']
                 ]);
    }

    public function test_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'status' => false,
                     'code' => 400,
                 ]);
    }
}
