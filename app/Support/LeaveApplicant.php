<?php

namespace App\Support;

use App\Models\User;

class LeaveApplicant
{
    public static function canApplyLeave(?User $user): bool
    {
        return in_array($user?->role ?? '', ['employee', 'manager'], true);
    }

    public static function isManager(?User $user): bool
    {
        return ($user?->role ?? '') === 'manager';
    }

    public static function layout(?User $user): string
    {
        return self::isManager($user) ? 'layouts.manager' : 'layouts.app';
    }

    public static function applyRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.apply' : 'apply';
    }

    public static function submitRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.leave.submit' : 'leave.submit';
    }

    public static function editRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.leave.edit' : 'leave.edit';
    }

    public static function updateRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.leave.update' : 'leave.update';
    }

    public static function myRequestsRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.myLeave' : 'leave.myRequests';
    }

    public static function personalDashboardRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.personal' : 'dashboard';
    }

    public static function balanceRoute(): string
    {
        return 'leave.balance';
    }

    public static function personalCalendarRoute(?User $user): string
    {
        return self::isManager($user) ? 'manager.myCalendar' : 'employee.calendar';
    }

    public static function approverLabel(?User $user): string
    {
        return self::isManager($user) ? 'a senior manager' : 'your manager';
    }
}
