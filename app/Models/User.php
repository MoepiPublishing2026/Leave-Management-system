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
        'is_active',
        'manager_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function directReports()
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /** Users who can submit leave requests. */
    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeAssignedToManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }
}
