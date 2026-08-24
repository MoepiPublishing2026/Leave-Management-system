<?php

namespace App\Notifications;

use App\Mail\SeniorLeaveDecisionMail;
use App\Models\LeaveRequest;
use App\Support\EmailGreeting;
use App\Support\LeavePortalLinks;
use App\Support\LeaveStatusLabels;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestUpdatedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(
        private LeaveRequest $leaveRequest,
        private bool $sentBackToManager = false
    ) {
        $this->leaveRequest->loadMissing('user');
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage|SeniorLeaveDecisionMail
    {
        if (strtolower((string) ($notifiable->role ?? '')) === 'admin'
            && LeaveStatusLabels::isStillPending($this->leaveRequest->status)
            && ! $this->sentBackToManager) {
            return (new SeniorLeaveDecisionMail($this->leaveRequest, EmailGreeting::displayName($notifiable)))
                ->to($notifiable->email);
        }

        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
        $type = $this->leaveRequest->leave_type ?: 'leave';

        $mail = SystemMailMessage::for($notifiable)
            ->subject('Leave request updated — #'.$this->leaveRequest->id)
            ->line("{$name} updated {$type} leave request #{$this->leaveRequest->id}.")
            ->line("Dates: {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date}.");

        if ($this->sentBackToManager) {
            $mail->line('Please review the updated request and submit a new recommendation.');
        } else {
            $mail->line('Please review the latest details before you decide.');
        }

        return $mail->action('Open in app', $this->actionUrl($notifiable));
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';

        return [
            'message' => $this->sentBackToManager
                ? "Leave request #{$this->leaveRequest->id} from {$name} was updated and needs a new recommendation."
                : "Leave request #{$this->leaveRequest->id} from {$name} was updated.",
            'leave_request_id' => $this->leaveRequest->id,
            'date' => now(),
        ];
    }

    private function actionUrl(object $notifiable): string
    {
        $role = strtolower((string) ($notifiable->role ?? ''));

        if ($role === 'admin') {
            return LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id);
        }

        return LeavePortalLinks::reviewUrlFor($notifiable instanceof \App\Models\User ? $notifiable : null, $this->leaveRequest->id);
    }
}
