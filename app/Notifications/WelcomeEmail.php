<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $plainPassword;

    public function __construct($user, $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ' . 'RPV System')
            ->greeting('Hello ' . $this->user->first_name . '!')
            ->line('Your account has been successfully created.')
            ->line('Here are your login credentials:')
            ->line('Username: ' . $this->user->username)
            ->line('Email: ' . $this->user->email)
            ->line('Password: ' . $this->plainPassword)
            ->action('Login to Your Account', url('/login'))
            ->line('Please change your password after first login.')
            ->line('If you have any questions, please contact support.');
    }
}