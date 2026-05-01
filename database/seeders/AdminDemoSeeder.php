<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@demo.test';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('Admin1234!'),
                'role' => 'admin',
                'is_verified' => true,
                'needs_password_change' => false,
                'status' => 'active',
            ]
        );
    }
}
