<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SubUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken($user)
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        return $response->json('data.access_token');
    }

    public function test_can_get_profile()
    {
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User',
            'email' => 'testprofile@example.com',
            'verified_at' => now(),
            'hashed_password' => Hash::make('password123'),
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'email' => 'testprofile@example.com'
                     ]
                 ]);
    }

    public function test_can_update_profile()
    {
        $user = SubUser::create([
            'id' => Str::uuid(),
            'fullname' => 'Test User',
            'email' => 'old@example.com',
            'verified_at' => now(),
            'hashed_password' => Hash::make('password123'),
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->putJson('/api/v1/auth/profile', [
            'email' => 'new@example.com',
            'password' => 'newpassword123'
        ], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sub_users', [
            'id' => $user->id,
            'email' => 'new@example.com'
        ]);
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->hashed_password));
    }
}
