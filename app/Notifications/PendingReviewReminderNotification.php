<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\LeaveWorkingDays;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PendingReviewReminderNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $stageLabel
    ) {
        $this->leaveRequest->loadMissing('user');
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $days = LeaveWorkingDays::count($this->leaveRequest);
        $role = strtolower((string) ($notifiable->role ?? ''));
        $stage = $this->stageLabel === 'daily' ? 'still pending (daily reminder)' : "pending for {$this->stageLabel}";

        $mail = (new MailMessage)
            ->subject('Reminder: Leave request #'.$this->leaveRequest->id.' still awaiting your review')
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line("{$name}'s {$type} leave request (#{$this->leaveRequest->id}) has been {$stage}.")
            ->line("Dates: {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date} ({$days} working day(s)).");

        if ($role === 'admin') {
            $url = URL::temporarySignedRoute(
                'leave.email.review',
                now()->addDays(7),
                ['leaveRequest' => $this->leaveRequest->id]
            );
            $mail->action('Review leave request', $url);
        } else {
            $mail->action('Open team requests', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
        $stage = $this->stageLabel === 'daily'
            ? 'still awaiting your review'
            : "awaiting your review for {$this->stageLabel}";

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'stage' => $this->stageLabel,
            'message' => "Reminder: {$name}'s leave request #{$this->leaveRequest->id} is {$stage}.",
            'date' => now(),
        ];
    }
}
