<?php

namespace App\Notifications;

use App\Models\Quarter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PedReadyForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Quarter $quarter,
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
            ->subject("Il tuo PED {$this->quarter->label} è pronto per la revisione")
            ->line("Il piano editoriale {$this->quarter->label} è pronto: puoi rivederlo, approvarlo o commentarlo.")
            ->action('Apri il PED', url("/ped/{$this->quarter->client->slug}"));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'PED pronto per la revisione',
            'message' => "Il piano editoriale {$this->quarter->label} è pronto per la revisione.",
            'url' => url("/ped/{$this->quarter->client->slug}"),
            'clientName' => $this->quarter->client->name,
        ];
    }
}
