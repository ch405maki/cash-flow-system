<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Request;

class NewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $requestModel;

    /**
     * Create a new notification instance.
     */
    public function __construct(Request $requestModel)
    {
        $this->requestModel = $requestModel;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Request Created: ' . $this->requestModel->request_no)
            ->markdown('emails.requests.new', [
                'requestModel' => $this->requestModel
            ]);
        }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}