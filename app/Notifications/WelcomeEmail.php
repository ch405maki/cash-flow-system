<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeEmail extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome!')
            ->greeting('Hello!')
            ->line('This is a test email notification.')
            ->action('Visit Website', url('/'))
            ->line('Thanks for testing!');
    }
}

