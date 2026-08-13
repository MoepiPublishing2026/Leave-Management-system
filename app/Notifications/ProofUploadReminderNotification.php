<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\LeaveProofRules;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ProofUploadReminderNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(
        private LeaveRequest $leaveRequest,
        private string $kind,
        private Carbon $referenceDate,
        private bool $sendMail = true
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable, $this->sendMail);
    }

    public function reminderKey(): string
    {
        return sprintf(
            'proof_%s_%s_%s',
            $this->leaveRequest->id,
            $this->kind,
            $this->referenceDate->toDateString()
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = LeaveProofRules::proofDeadlineAt($this->leaveRequest)?->format('d M Y') ?? 'the deadline';

        $mail = (new MailMessage)
            ->subject($this->subjectLine())
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line($this->messageFor())
            ->line('Upload your doctor\'s letter from My Leave Requests by '.$deadline.' at the latest.')
            ->action('Upload document', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));

        if ($this->kind === 'deadline_today') {
            $mail->line('If the letter is not uploaded today, this approved sick leave will be changed to unpaid leave.');
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'reminder_key' => $this->reminderKey(),
            'kind' => $this->kind,
            'message' => $this->messageFor(),
            'date' => now(),
        ];
    }

    private function subjectLine(): string
    {
        return match ($this->kind) {
            'deadline_today' => 'Upload your doctor\'s letter today — request #'.$this->leaveRequest->id,
            'deadline_tomorrow' => 'Doctor\'s letter due tomorrow — request #'.$this->leaveRequest->id,
            default => 'Upload your doctor\'s letter — request #'.$this->leaveRequest->id,
        };
    }

    private function messageFor(): string
    {
        $deadline = LeaveProofRules::proofDeadlineAt($this->leaveRequest)?->format('d M Y') ?? 'the deadline';

        return match ($this->kind) {
            'approved' => 'Your sick leave was approved. Upload your doctor\'s letter by '.$deadline.'.',
            'window_open' => 'You can now upload your doctor\'s letter for sick leave request #'.$this->leaveRequest->id.'. Deadline: '.$deadline.'.',
            'deadline_tomorrow' => 'Your doctor\'s letter for sick leave request #'.$this->leaveRequest->id.' is due tomorrow ('.$deadline.').',
            'deadline_today' => 'Today is the last day to upload your doctor\'s letter for sick leave request #'.$this->leaveRequest->id.'.',
            default => 'Please upload your doctor\'s letter for leave request #'.$this->leaveRequest->id.' by '.$deadline.'.',
        };
    }
}
