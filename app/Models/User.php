<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Support\PerformanceReviews;
use App\Support\PersonName;
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

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if (! $user->wasChanged(['first_name', 'last_name'])) {
                return;
            }

            $name = PerformanceReviews::displayName($user);
            if ($name === '') {
                return;
            }

            PerformanceReview::query()
                ->where('employee_id', $user->id)
                ->update(['employee_name' => $name]);

            PerformanceReview::query()
                ->where('reviewer_id', $user->id)
                ->update(['reviewer_name' => $name]);
        });
    }

    public function setFirstNameAttribute(?string $value): void
    {
        $this->attributes['first_name'] = PersonName::normalize($value);
    }

    public function setLastNameAttribute(?string $value): void
    {
        $this->attributes['last_name'] = PersonName::normalize($value);
    }

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
