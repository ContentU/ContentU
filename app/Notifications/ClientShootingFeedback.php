<?php

namespace App\Notifications;

use App\Models\Client;
use App\Models\ShootingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ClientShootingFeedback extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Client $client,
        public ShootingSession $session,
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
            ->subject("{$this->client->name}: sessione shooting {$this->azione}")
            ->line("Il cliente {$this->client->name} ha {$this->azione} la sessione shooting del ".$this->session->session_date->translatedFormat('j M Y').'.')
            ->action('Apri lo shooting', route('shooting.index'))
            ->line('Puoi rispondere dal pannello shooting.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Sessione shooting {$this->azione}",
            'message' => "Il cliente {$this->client->name} ha {$this->azione} la sessione shooting del ".$this->session->session_date->translatedFormat('j M Y').'.',
            'url' => route('shooting.index'),
            'clientName' => $this->client->name,
            'excerpt' => $this->comment ? Str::limit($this->comment, 140) : null,
        ];
    }
}
