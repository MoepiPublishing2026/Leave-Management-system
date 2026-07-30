<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ManagerTeam
{
    /** Employee IDs assigned to this manager. */
    public static function employeeIdsFor(?User $manager = null): Collection
    {
        $manager = $manager ?? Auth::user();
        if (! $manager || strtolower((string) ($manager->role ?? '')) !== 'manager') {
            return collect();
        }

        return User::query()
            ->employees()
            ->where('manager_id', $manager->id)
            ->pluck('id');
    }

    public static function employeesFor(?User $manager = null): Collection
    {
        $manager = $manager ?? Auth::user();
        if (! $manager || strtolower((string) ($manager->role ?? '')) !== 'manager') {
            return collect();
        }

        return User::query()
            ->employees()
            ->where('manager_id', $manager->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'department', 'email']);
    }

    /** Restrict leave queries to employees assigned to the manager. */
    public static function scopeLeaveRequests(Builder $query, ?User $manager = null): Builder
    {
        $ids = self::employeeIdsFor($manager);

        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('user_id', $ids);
    }

    public static function leaveQuery(?User $manager = null): Builder
    {
        return self::scopeLeaveRequests(
            LeaveRequest::query()->byEmployeeApplicants(),
            $manager
        );
    }
}
