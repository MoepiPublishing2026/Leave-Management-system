<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeaveApplicant;
use App\Support\LeaveReminderMessage;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class LeaveReminderNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $kind,
        public Carbon $referenceDate
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function reminderKey(): string
    {
        return sprintf(
            'leave_%s_%s_%s',
            $this->leaveRequest->id,
            $this->kind,
            $this->referenceDate->toDateString()
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $body = LeaveReminderMessage::build($notifiable, $this->leaveRequest, $this->kind, $this->referenceDate, false);
        $role = strtolower((string) ($notifiable->role ?? 'employee'));
        $isTeam = str_starts_with($this->kind, 'team_');

        $url = match (true) {
            $isTeam && $role === 'admin' => route('admin.calendar'),
            $isTeam => route('manager.calendar'),
            default => route(LeaveApplicant::personalCalendarRoute($notifiable)),
        };

        $subject = match ($this->kind) {
            'today', 'team_today' => 'Leave reminder: today',
            'tomorrow', 'team_tomorrow' => 'Leave reminder: tomorrow',
            default => 'Upcoming leave reminder',
        };

        return SystemMailMessage::for($notifiable)
            ->subject($subject)
            ->line($body)
            ->action('Open in app', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'reminder_key' => $this->reminderKey(),
            'kind' => $this->kind,
            'message' => LeaveReminderMessage::build($notifiable, $this->leaveRequest, $this->kind, $this->referenceDate),
            'date' => now(),
        ];
    }
}
