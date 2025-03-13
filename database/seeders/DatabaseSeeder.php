<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creating an admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Change this to your admin email
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'), // Change this to a secure password
                'role' => 'admin',
            ]
        );

        // Creating a regular user
        User::updateOrCreate(
            ['email' => 'user@example.com'], 
            [
                'name' => 'Regular User',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );
    }
}
