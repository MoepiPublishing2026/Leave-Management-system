<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\LeaveStatusLabels;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeaveRequestNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(public LeaveRequest $leaveRequest)
    {
        $this->leaveRequest->loadMissing('user');
    }

    public function via(object $notifiable): array
    {
        // Senior managers already get the rich SeniorLeaveDecisionMail with a signed review link.
        // Keep this notification as in-app for them to avoid duplicate emails.
        if (strtolower((string) ($notifiable->role ?? '')) === 'admin') {
            return ['database'];
        }

        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $start = $this->leaveRequest->start_date;
        $end = $this->leaveRequest->end_date;

        return (new MailMessage)
            ->subject('New leave request — #'.$this->leaveRequest->id)
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line("{$name} submitted a {$type} leave request (#{$this->leaveRequest->id}).")
            ->line("Dates: {$start} to {$end}.")
            ->line('Please review it as soon as you can.')
            ->action('Open team requests', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
        $status = LeaveStatusLabels::display($this->leaveRequest->status);
        $isSeniorQueue = strtolower(trim((string) ($this->leaveRequest->status ?? ''))) === 'pending_senior';

        $message = $isSeniorQueue
            ? "Leave request #{$this->leaveRequest->id} from {$name} is awaiting your approval ({$status})."
            : "New leave request from {$name}";

        return [
            'message' => $message,
            'leave_request_id' => $this->leaveRequest->id,
            'date' => now(),
        ];
    }
}
