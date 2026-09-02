<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Support\EmailGreeting;
use App\Support\LeavePortalLinks;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OverdueLeaveMail extends Mailable
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
        $role = $this->role();

        return $this->subject('Leave request overdue — #'.$this->leaveRequest->id)
            ->view('emails.leave_request_overdue', [
                'leaveRequest' => $this->leaveRequest,
                'notifiable' => $this->notifiable,
                'role' => $role,
                'recipientFirstName' => EmailGreeting::displayName($this->notifiable),
                'reviewUrl' => $this->reviewUrl(),
                'messageBody' => $this->messageBody(),
            ]);
    }

    private function role(): string
    {
        return strtolower((string) ($this->notifiable->role ?? 'employee'));
    }

    private function reviewUrl(): string
    {
        $role = $this->role();
        $params = [
            'status' => 'overdue',
            'search' => (string) $this->leaveRequest->id,
            'request_id' => $this->leaveRequest->id,
        ];

        return match ($role) {
            'admin' => route('admin.requests', $params),
            'manager' => route('manager.requests.index', $params),
            default => LeavePortalLinks::myRequestsUrl($this->notifiable, $this->leaveRequest->id),
        };
    }

    public function messageBody(): string
    {
        $name = EmailGreeting::displayName($this->leaveRequest->user, 'Employee');
        $role = $this->role();
        $withSenior = strtolower(trim((string) ($this->leaveRequest->status ?? ''))) === 'pending_senior'
            || $this->leaveRequest->isManagerApplicant();
        $type = (string) ($this->leaveRequest->leave_type ?? 'leave');

        return match ($role) {
            'manager' => $withSenior
                ? "{$name}'s {$type} leave request is overdue and has been sent to the senior manager."
                : "{$name}'s {$type} leave request is overdue and requires your attention.",
            'admin' => "{$name}'s {$type} leave start date has passed without a decision. This request can no longer be approved or rejected.",
            default => $withSenior
                ? "Your {$type} leave request is overdue and is still awaiting a senior manager decision."
                : "Your {$type} leave request is overdue because it is still awaiting manager review.",
        };
    }
}
