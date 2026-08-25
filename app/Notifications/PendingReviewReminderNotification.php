<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\EmailGreeting;
use App\Support\LeaveApprovalWorkflow;
use App\Support\LeavePortalLinks;
use App\Support\LeaveWorkingDays;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $name = EmailGreeting::displayName($applicant, 'an employee');
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $days = LeaveWorkingDays::count($this->leaveRequest);
        $role = strtolower((string) ($notifiable->role ?? ''));
        $stage = $this->stageLabel === 'daily' ? 'still pending (daily reminder)' : "pending for {$this->stageLabel}";
        $context = LeaveApprovalWorkflow::reportingContext($this->leaveRequest);

        $mail = SystemMailMessage::for($notifiable)
            ->subject('Reminder: Leave request #'.$this->leaveRequest->id.' still awaiting your review')
            ->line("{$name}'s {$type} leave request (#{$this->leaveRequest->id}) has been {$stage}.")
            ->line("Dates: {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date} ({$days} working day(s)).");

        if ($role === 'admin' && $context['manager_name'] !== '' && ! $context['is_manager_applicant']) {
            $mail->line('Assigned manager: '.$context['manager_name'].'.');
        }

        if ($role === 'admin') {
            $mail->action('Open in app', LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id));
        } else {
            $mail->action('Open in app', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = EmailGreeting::displayName($applicant, 'an employee');
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
