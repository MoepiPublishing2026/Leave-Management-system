<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens;

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
        'manager_id' => 'integer',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function directReports()
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function managedDepartments()
    {
        return $this->hasMany(ManagerDepartment::class, 'user_id');
    }

    /** @return list<string> */
    public function managedDepartmentNames(): array
    {
        return $this->managedDepartments()
            ->orderBy('department')
            ->pluck('department')
            ->map(fn ($d) => (string) $d)
            ->values()
            ->all();
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
