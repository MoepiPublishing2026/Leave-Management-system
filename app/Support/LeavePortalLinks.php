<?php

namespace App\Support;

use App\Models\User;

class LeavePortalLinks
{
    public static function myRequestsUrl(?User $user, ?int $requestId = null): string
    {
        $params = ['status' => 'all'];
        if ($requestId) {
            $params['search'] = (string) $requestId;
            $params['request_id'] = $requestId;
        }

        $role = strtolower(trim((string) ($user->role ?? 'employee')));

        return match ($role) {
            'manager' => route('manager.myLeave', $params),
            'admin' => route('admin.requests', $params),
            default => route('leave.myRequests', $params),
        };
    }

    public static function teamRequestsUrl(?int $requestId = null): string
    {
        $params = ['status' => 'all'];
        if ($requestId) {
            $params['search'] = (string) $requestId;
            $params['request_id'] = $requestId;
        }

        return route('manager.requests.index', $params);
    }
}
