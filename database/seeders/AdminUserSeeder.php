<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
    ['email' => 'admin@moepi.com'],
    [
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@moepi.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'department' => 'Support',
        'designation' => 'Support',
    ]
);

    }
}
