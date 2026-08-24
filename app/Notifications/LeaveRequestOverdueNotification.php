<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestOverdueNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = strtolower((string) ($notifiable->role ?? 'employee'));
        $url = match ($role) {
            'admin' => route('admin.requests', ['status' => 'all', 'search' => (string) $this->leaveRequest->id, 'request_id' => $this->leaveRequest->id]),
            'manager' => LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id),
            default => LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id),
        };

        return SystemMailMessage::for($notifiable)
            ->subject('Leave request overdue — #'.$this->leaveRequest->id)
            ->line($this->messageFor($notifiable))
            ->action('Open in app', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $this->messageFor($notifiable),
            'overdue_at' => $this->leaveRequest->start_date,
        ];
    }

    private function messageFor(object $notifiable): string
    {
        $employeeName = trim(
            ($this->leaveRequest->user->first_name ?? '').' '.
            ($this->leaveRequest->user->last_name ?? '')
        );
        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';
        $role = strtolower((string) ($notifiable->role ?? 'employee'));

        return match ($role) {
            'manager' => "{$employeeName}'s {$this->leaveRequest->leave_type} leave request is overdue and requires your attention.",
            'admin' => "{$employeeName}'s {$this->leaveRequest->leave_type} leave request is overdue and is awaiting manager action.",
            default => "Your {$this->leaveRequest->leave_type} leave request is overdue because it is still awaiting manager review.",
        };
    }
}
