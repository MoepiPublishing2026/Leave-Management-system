<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueLeaveEscalatedNotification extends Notification
{
    use Queueable;

    private LeaveRequest $leaveRequest;
    private string $managerName;

    public function __construct(LeaveRequest $leaveRequest, string $managerName)
    {
        $this->leaveRequest = $leaveRequest;
        $this->managerName = $managerName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employeeName = trim(($this->leaveRequest->user->first_name ?? '') . ' ' . ($this->leaveRequest->user->last_name ?? ''));

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => 'Unattended leave request: ' . ($employeeName !== '' ? $employeeName : 'Employee') . ' (' . $this->leaveRequest->leave_type . ')',
            'manager_name' => $this->managerName,
            'late_response_reason' => $this->leaveRequest->overdue_escalation_reason,
        ];
    }
}
