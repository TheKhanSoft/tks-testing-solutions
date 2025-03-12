<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create regular staff users
        $users = [
            [
                'name' => 'Muhammad Ahsan',
                'email' => 'ahsan@example.com', 
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Ayesha Khan',
                'email' => 'ayesha@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Farhan Ahmed',
                'email' => 'farhan@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Zainab Ali',
                'email' => 'zainab@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData + ['email_verified_at' => now()]);
        }
    }
}
