<?php

namespace App\Support;

class LeaveStatusLabels
{
    public static function display(?string $status, bool $isOverdue = false): string
    {
        if ($isOverdue) {
            return 'Overdue';
        }

        return match (strtolower(trim((string) $status))) {
            // Until final senior decision, everyone sees Pending (including after manager recommend)
            'pending', 'pending_senior' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            'completed' => 'Completed',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : 'Unknown',
        };
    }

    public static function badgeClass(?string $status, bool $isOverdue = false): string
    {
        if ($isOverdue) {
            return 'overdue';
        }

        $status = strtolower(trim((string) $status));

        return match ($status) {
            'pending_senior', 'pending' => 'pending',
            'withdrawn' => 'withdrawn',
            'approved', 'rejected' => $status,
            default => 'pending',
        };
    }

    public static function isStillPending(?string $status): bool
    {
        return in_array(strtolower(trim((string) $status)), ['pending', 'pending_senior'], true);
    }
}
