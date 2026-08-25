<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $recipientFirstName;

    public function __construct($code, $recipientFirstName = null)
    {
        $this->code = $code;
        $this->recipientFirstName = $recipientFirstName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Two-Factor Authentication Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two_factor_code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
