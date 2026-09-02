<?php

namespace App\Notifications;

use App\Mail\ProofUploadedMail;
use App\Models\LeaveRequest;
use App\Support\LeaveProofRules;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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

    public function toMail(object $notifiable): Mailable
    {
        return (new ProofUploadedMail($this->leaveRequest, $notifiable))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $mail = new ProofUploadedMail($this->leaveRequest, $notifiable);
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'An employee';
        $isDoctorsLetter = LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type);

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $isDoctorsLetter
                ? "{$name} uploaded a doctor's letter for leave request #{$this->leaveRequest->id}."
                : "{$name} updated documents for leave request #{$this->leaveRequest->id}.",
            'date' => now(),
            'mail_preview' => $mail->messageBody(),
        ];
    }
}
