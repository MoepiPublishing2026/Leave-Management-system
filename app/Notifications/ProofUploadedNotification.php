<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\LeaveProofRules;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProofUploadedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private LeaveRequest $leaveRequest)
    {
        $this->leaveRequest->loadMissing('user');
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'An employee';
        $isDoctorsLetter = LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type);
        $label = $isDoctorsLetter ? "a doctor's letter" : 'supporting documents';

        return SystemMailMessage::for($notifiable)
            ->subject(($isDoctorsLetter ? 'Doctor\'s letter uploaded' : 'Documents updated').' — request #'.$this->leaveRequest->id)
            ->line("{$name} uploaded {$label} for leave request #{$this->leaveRequest->id}.")
            ->line($this->isSenior($notifiable)
                ? 'Please open the request to review the latest documents, then approve or reject.'
                : 'Please review the latest documents.')
            ->action('Open in app', $this->actionUrl($notifiable));
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'An employee';
        $isDoctorsLetter = LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type);

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $isDoctorsLetter
                ? "{$name} uploaded a doctor's letter for leave request #{$this->leaveRequest->id}."
                : "{$name} updated documents for leave request #{$this->leaveRequest->id}.",
            'date' => now(),
        ];
    }

    private function isSenior(object $notifiable): bool
    {
        return strtolower((string) ($notifiable->role ?? '')) === 'admin';
    }

    private function actionUrl(object $notifiable): string
    {
        if ($this->isSenior($notifiable)) {
            return LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id);
        }

        return LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id);
    }
}
