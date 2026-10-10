<?php

namespace App\Support;

use App\Models\User;

class SupportStaff
{
    public const DEPARTMENT = 'Support';

    public static function canManageEmployees(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return strcasecmp(trim((string) $user->department), self::DEPARTMENT) === 0;
    }
}
