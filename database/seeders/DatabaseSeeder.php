<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'adminwr',
            'first_name' => 'Admin',
            'middle_initial' => null,
            'last_name' => 'User',
            'name' => 'Admin User',
            'email' => 'tyomata123@gmail.com',
            'phone' => '09123456789',
            'address' => 'CDO',
            'role' => 'admin',
            'is_active' => 1,
            'password' => 'Admin12345!',
        ]);

        User::create([
            'username' => 'hrwr',
            'first_name' => 'HR',
            'middle_initial' => null,
            'last_name' => 'Officer',
            'name' => 'HR Officer',
            'email' => 'hr@example.com',
            'phone' => '09123456780',
            'address' => 'CDO',
            'role' => 'hr',
            'is_active' => 1,
            'password' => 'Hr123456!',
        ]);

        User::create([
            'username' => 'clientwr',
            'first_name' => 'Client',
            'middle_initial' => null,
            'last_name' => 'User',
            'name' => 'Client User',
            'email' => 'client@example.com',
            'phone' => '09123456781',
            'address' => 'CDO',
            'role' => 'client',
            'is_active' => 1,
            'password' => 'Client12345!',
        ]);
    }
}