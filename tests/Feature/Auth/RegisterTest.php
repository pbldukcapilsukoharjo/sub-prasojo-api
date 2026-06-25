<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SubUser;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_new_user()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'fullname' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'code',
                     'message',
                     'data' => [
                         'id',
                         'fullname',
                         'email',
                         'created_at'
                     ]
                 ]);

        $this->assertDatabaseHas('sub_users', [
            'email' => 'johndoe@example.com'
        ]);
    }

    public function test_cannot_register_with_existing_email()
    {
        SubUser::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'fullname' => 'Existing User',
            'email' => 'johndoe@example.com',
            'hashed_password' => \Illuminate\Support\Facades\Hash::make('password123')
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'fullname' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
