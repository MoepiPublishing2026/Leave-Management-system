<?php

namespace App\Support;

class RoleLabels
{
    public static function display(?string $role): string
    {
        return match (strtolower(trim((string) $role))) {
            'admin' => 'Senior Manager',
            'manager' => 'Manager',
            'employee' => 'Employee',
            default => $role ? ucfirst($role) : 'User',
        };
    }
}
