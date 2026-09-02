<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\EmailGreeting;
use App\Support\LeaveAttachmentLinks;
use App\Support\LeavePortalLinks;
use App\Support\LeaveProofRules;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProofUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public object $notifiable
    ) {
        $this->leaveRequest->loadMissing('user');
    }

    public function build()
    {
        $recipient = $this->notifiable instanceof User ? $this->notifiable : null;
        $links = LeaveAttachmentLinks::forEmailRequest($this->leaveRequest, 14);
        $mail = $this->subject($this->subjectLine())
            ->view('emails.proof_uploaded', [
                'leaveRequest' => $this->leaveRequest,
                'notifiable' => $this->notifiable,
                'recipientFirstName' => EmailGreeting::displayName($this->notifiable),
                'applicantName' => EmailGreeting::displayName($this->leaveRequest->user, 'An employee'),
                'isDoctorsLetter' => LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type),
                'needsDecision' => $this->needsDecision(),
                'documentLinks' => $links,
                'reviewUrl' => $this->signedReviewUrl($recipient),
                'openInAppUrl' => LeavePortalLinks::reviewUrlFor($recipient, $this->leaveRequest->id),
                'messageBody' => $this->messageBody(),
            ]);

        $disk = Storage::disk('public');
        foreach (LeaveAttachmentLinks::paths($this->leaveRequest) as $path) {
            $relative = ltrim((string) $path, '/');
            if ($relative === '' || ! $disk->exists($relative)) {
                continue;
            }

            $size = (int) $disk->size($relative);
            if ($size < 1 || $size > 8 * 1024 * 1024) {
                continue;
            }

            $options = ['as' => basename($relative) ?: 'document'];
            $mime = $disk->mimeType($relative);
            if (is_string($mime) && $mime !== '') {
                $options['mime'] = $mime;
            }

            $mail->attachFromStorageDisk('public', $relative, $options['as'], $options);
        }

        return $mail;
    }

    private function signedReviewUrl(?User $recipient): ?string
    {
        if (! $recipient || strtolower((string) ($recipient->role ?? '')) !== 'admin') {
            return null;
        }

        return LeavePortalLinks::seniorEmailReviewUrl($this->leaveRequest, 14, $recipient);
    }

    private function subjectLine(): string
    {
        $id = $this->leaveRequest->id;
        if (LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type)) {
            return 'Doctor\'s letter uploaded — request #'.$id;
        }

        return 'Documents updated — request #'.$id;
    }

    public function messageBody(): string
    {
        $name = EmailGreeting::displayName($this->leaveRequest->user, 'An employee');
        $id = $this->leaveRequest->id;
        $isDoctorsLetter = LeaveProofRules::allowsDeferredProof($this->leaveRequest->leave_type);
        $label = $isDoctorsLetter ? "a doctor's letter" : 'supporting documents';

        if ($this->needsDecision()) {
            return "{$name} uploaded {$label} for leave request #{$id}. Please review the document, then approve or reject.";
        }

        return "{$name} uploaded {$label} for leave request #{$id}. You can view it from this email.";
    }

    private function needsDecision(): bool
    {
        $status = strtolower(trim((string) ($this->leaveRequest->status ?? '')));

        return in_array($status, ['pending', 'pending_senior'], true)
            && ! $this->leaveRequest->isOverdue();
    }
}
