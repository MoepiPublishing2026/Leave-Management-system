<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // <-- Add Notifiable here

    protected $fillable = [
        'first_name',
        'last_name',
        'department',
        'designation',
        'email',
        'password',
        'role',  // 'admin', 'manager', 'employee'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
