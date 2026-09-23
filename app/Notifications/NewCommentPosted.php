<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentPosted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Content $content,
        public Comment $comment,
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
            ->subject("Nuovo commento su «{$this->content->title}»")
            ->line("{$this->comment->authorLabel()} ha scritto: {$this->comment->body}")
            ->action('Apri il trimestre', route('quarters.show', $this->content->quarter_id));
    }
}
