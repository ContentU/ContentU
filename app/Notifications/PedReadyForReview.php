<?php

namespace App\Notifications;

use App\Models\ClientPublicLink;
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
        public ClientPublicLink $link,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Il tuo PED {$this->quarter->label} è pronto per la revisione")
            ->line("Il piano editoriale {$this->quarter->label} è pronto: puoi rivederlo, approvarlo o commentarlo.")
            ->action('Apri il PED', url("/ped/{$this->link->token}"));
    }
}
