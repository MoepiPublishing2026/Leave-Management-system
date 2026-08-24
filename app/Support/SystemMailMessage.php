<?php

namespace App\Support;

use Illuminate\Notifications\Messages\MailMessage;

class SystemMailMessage extends MailMessage
{
    public static function for(object $notifiable): self
    {
        $mail = new self();
        $mail->greeting(EmailGreeting::helloUser($notifiable));
        $mail->salutation("Regards,  \nMoepi Leave Management System");

        return $mail;
    }
}
