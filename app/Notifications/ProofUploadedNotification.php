<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProofUploadedNotification extends Notification
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
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'An employee';
        $role = strtolower((string) ($notifiable->role ?? ''));
        $url = $role === 'admin'
            ? route('admin.requests', ['status' => 'all', 'search' => (string) $this->leaveRequest->id, 'request_id' => $this->leaveRequest->id])
            : LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id);

        return (new MailMessage)
            ->subject('Doctor\'s letter uploaded — request #'.$this->leaveRequest->id)
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line("{$name} uploaded a doctor's letter for leave request #{$this->leaveRequest->id}.")
            ->action('View request', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'An employee';

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => "{$name} uploaded a doctor's letter for leave request #{$this->leaveRequest->id}.",
            'date' => now(),
        ];
    }
}
