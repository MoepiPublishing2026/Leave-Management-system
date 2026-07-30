<?php

namespace App\Support;

use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OverdueLeaveEscalation
{
    public static function pendingReasonQueue(): Collection
    {
        return LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
            ->needsOverdueEscalationReason()
            ->with('user')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->map(function (LeaveRequest $request) {
                $employee = trim(($request->user->first_name ?? '').' '.($request->user->last_name ?? ''));

                return [
                    'id' => $request->id,
                    'employee' => $employee !== '' ? $employee : 'Employee',
                    'type' => (string) ($request->leave_type ?? 'Leave'),
                    'start' => $request->start_date ? Carbon::parse($request->start_date)->format('d M Y') : '',
                    'end' => $request->end_date ? Carbon::parse($request->end_date)->format('d M Y') : '',
                ];
            })
            ->values();
    }
}
