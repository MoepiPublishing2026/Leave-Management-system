<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(private LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employeeName = trim(
            ($this->leaveRequest->user->first_name ?? '').' '.
            ($this->leaveRequest->user->last_name ?? '')
        );
        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';
        $role = strtolower((string) ($notifiable->role ?? 'employee'));

        $message = match ($role) {
            'manager' => "{$employeeName}'s {$this->leaveRequest->leave_type} leave request is overdue and requires your attention.",
            'admin' => "{$employeeName}'s {$this->leaveRequest->leave_type} leave request is overdue and is awaiting manager action.",
            default => "Your {$this->leaveRequest->leave_type} leave request is overdue because it is still awaiting manager review.",
        };

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $message,
            'overdue_at' => $this->leaveRequest->start_date,
        ];
    }
}
