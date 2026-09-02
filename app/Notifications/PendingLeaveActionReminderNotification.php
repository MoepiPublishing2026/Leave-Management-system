<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\EmailGreeting;
use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingLeaveActionReminderNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(
        private LeaveRequest $leaveRequest,
        private int $daysUntilStart
    ) {
        $this->leaveRequest->loadMissing('user');
    }

    public function reminderKey(): string
    {
        return sprintf('pending_deadline_%s_%s', $this->leaveRequest->id, $this->daysUntilStart);
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = strtolower((string) ($notifiable->role ?? ''));
        $url = $role === 'admin'
            ? LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id)
            : LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id);

        return SystemMailMessage::for($notifiable)
            ->subject('Action needed before leave starts — #'.$this->leaveRequest->id)
            ->line($this->messageFor($notifiable))
            ->line($this->datesLine())
            ->line('Please review this request before the start date.')
            ->action('Open in app', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'reminder_key' => $this->reminderKey(),
            'days_until_start' => $this->daysUntilStart,
            'message' => $this->messageFor($notifiable),
        ];
    }

    private function datesLine(): string
    {
        $type = $this->leaveRequest->leave_type ?: 'leave';

        return ucfirst($type).' from '.$this->leaveRequest->start_date.' to '.$this->leaveRequest->end_date.'.';
    }

    private function whenLabel(): string
    {
        return match ($this->daysUntilStart) {
            0 => 'today',
            1 => 'tomorrow',
            default => 'in '.$this->daysUntilStart.' days',
        };
    }

    private function messageFor(object $notifiable): string
    {
        $name = EmailGreeting::displayName($this->leaveRequest->user, 'An employee');
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $when = $this->whenLabel();
        $role = strtolower((string) ($notifiable->role ?? ''));
        $withSenior = strtolower(trim((string) ($this->leaveRequest->status ?? ''))) === 'pending_senior'
            || $this->leaveRequest->isManagerApplicant();

        return match ($role) {
            'admin' => $withSenior
                ? "{$name}'s {$type} leave starts {$when} and still needs your decision."
                : "{$name}'s {$type} leave starts {$when} and is still awaiting manager action.",
            default => $withSenior
                ? "{$name}'s {$type} leave starts {$when} and is with the senior manager."
                : "{$name}'s {$type} leave starts {$when} and still needs your review.",
        };
    }
}
