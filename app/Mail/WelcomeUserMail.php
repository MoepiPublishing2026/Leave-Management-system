<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $rawPassword;

    public function __construct($user, $rawPassword)
    {
        $this->user = $user;
        $this->rawPassword = $rawPassword;
    }

    public function build()
    {
        return $this->subject('Welcome to the Leave Management System')
                    ->view('emails.welcome_user');
    }
}
