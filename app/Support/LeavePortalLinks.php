<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\URL;

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

    public static function seniorEmailReviewUrl(LeaveRequest $leave, int $daysValid = 7, ?User $senior = null): string
    {
        return URL::temporarySignedRoute(
            'leave.email.review',
            now()->addDays($daysValid),
            \App\Support\LeaveApprovalWorkflow::emailDecisionRouteParams($leave, $senior)
        );
    }

    public static function seniorInAppUrl(?int $requestId = null): string
    {
        $params = ['status' => 'pending'];
        if ($requestId) {
            $params['search'] = (string) $requestId;
            $params['request_id'] = $requestId;
        }

        return route('admin.requests', $params);
    }

    public static function reviewUrlFor(?User $user, ?int $requestId = null): string
    {
        $role = strtolower(trim((string) ($user->role ?? 'employee')));

        return match ($role) {
            'admin' => self::myRequestsUrl($user, $requestId),
            'manager' => self::teamRequestsUrl($requestId),
            default => self::myRequestsUrl($user, $requestId),
        };
    }
}
