<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertRaised extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Alert $alert,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Alert: {$this->alert->type->label()}")
            ->line($this->alert->message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->alert->type->label(),
            'message' => $this->alert->message,
            'url' => null,
            'clientName' => $this->alert->client->name,
        ];
    }
}
