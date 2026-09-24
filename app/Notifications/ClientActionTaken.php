<?php

namespace App\Notifications;

use App\Models\Client;
use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ClientActionTaken extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Client $client,
        public Content $content,
        public string $azione,
        public ?string $comment = null,
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
            ->subject("{$this->client->name}: contenuto {$this->azione}")
            ->line("Il cliente {$this->client->name} ha {$this->azione} il contenuto «{$this->content->title}».")
            ->action('Apri il trimestre', route('quarters.show', $this->content->quarter_id))
            ->line('Puoi operare le modifiche richieste dalla dashboard.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Contenuto {$this->azione}",
            'message' => "Il cliente {$this->client->name} ha {$this->azione} il contenuto «{$this->content->title}».",
            'url' => route('quarters.show', $this->content->quarter_id),
            'clientName' => $this->client->name,
            'excerpt' => $this->comment ? Str::limit($this->comment, 140) : null,
        ];
    }
}
