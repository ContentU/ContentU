<?php

namespace App\Notifications;

use App\Models\TopicPreview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopicPreviewResponded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TopicPreview $preview,
        public string $status,
        public ?string $comment,
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
        $labels = [
            'approved' => 'approvato',
            'approved_with_notes' => 'approvato con modifiche',
            'revise' => 'da rivedere',
        ];

        $mail = (new MailMessage)
            ->subject("Argomenti {$this->preview->month_label}: {$labels[$this->status]}")
            ->line("Il cliente ha segnato gli argomenti di {$this->preview->month_label} come «{$labels[$this->status]}».")
            ->action('Apri il trimestre', route('quarters.show', $this->preview->quarter_id));

        if ($this->comment) {
            $mail->line("Commento: {$this->comment}");
        }

        return $mail;
    }
}
