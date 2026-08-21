<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueLeaveEscalatedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    private LeaveRequest $leaveRequest;
    private string $managerName;

    public function __construct(LeaveRequest $leaveRequest, string $managerName)
    {
        $this->leaveRequest = $leaveRequest;
        $this->managerName = $managerName;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = trim(($this->leaveRequest->user->first_name ?? '').' '.($this->leaveRequest->user->last_name ?? ''));
        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';

        return (new MailMessage)
            ->subject('Unattended leave request escalated — #'.$this->leaveRequest->id)
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line("{$this->managerName} flagged an unattended leave request for {$employeeName} ({$this->leaveRequest->leave_type}).")
            ->line('Late response reason: '.((string) ($this->leaveRequest->overdue_escalation_reason ?: 'Not provided')))
            ->action('Review request', route('admin.requests', [
                'status' => 'all',
                'search' => (string) $this->leaveRequest->id,
                'request_id' => $this->leaveRequest->id,
            ]));
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
