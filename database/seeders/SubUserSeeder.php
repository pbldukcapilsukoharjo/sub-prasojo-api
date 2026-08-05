<?php

namespace Database\Seeders;

use App\Models\Monitoring\SubUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SubUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubUser::create([
            'fullname' => 'Admin',
            'email' => 'admin@gmail.com',
            'hashed_password' => Hash::driver('argon2id')->make('admin123'),
            'verified_at' => now(),
        ]);

        SubUser::create([
            'fullname' => 'Shiro',
            'email' => 'shiro@gmail.com',
            'hashed_password' => Hash::driver('argon2id')->make('shiro123'),
            'verified_at' => now(),
        ]);

        SubUser::create([
            'fullname' => 'Admin Prasojo',
            'email' => 'admin@prasojo.com',
            'hashed_password' => Hash::driver('argon2id')->make('password'),
            'verified_at' => now(),
        ]);
    }
}
