<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat User Admin
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'security_question' => 'What is your pet name?',
            'security_answer' => Hash::make('fluffy'),
        ]);

        // Membuat User Staff
        User::create([
            'name' => 'Staff User',
            'username' => 'staff',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'security_question' => 'What is your favorite color?',
            'security_answer' => Hash::make('blue'),
        ]);
    }
}