<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:make-admin {email}', function () {
    $email = trim((string) $this->argument('email'));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Invalid email.');
        return 1;
    }

    $user = User::where('email', $email)->first();

    if ($user) {
        $user->role = 'admin';
        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = true;
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $user->is_active = true;
        }
        $user->save();
        $this->info("Updated user to admin: {$user->email} (id: {$user->id})");
        return 0;
    }

    $password = method_exists(Str::class, 'password') ? Str::password(16) : Str::random(16);
    $data = [
        'first_name' => 'Rosina',
        'last_name' => 'Admin',
        'department' => null,
        'designation' => 'Admin',
        'email' => $email,
        'password' => Hash::make($password),
        'role' => 'admin',
    ];
    if (Schema::hasColumn('users', 'is_admin')) {
        $data['is_admin'] = true;
    }
    if (Schema::hasColumn('users', 'is_active')) {
        $data['is_active'] = true;
    }

    $user = User::create($data);
    $this->info("Created admin user: {$user->email} (id: {$user->id})");
    $this->line("Temporary password: {$password}");
    return 0;
})->purpose('Create or promote a user to admin');
