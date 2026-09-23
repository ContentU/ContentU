<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// TODO v1.1: thread di risposta ai commenti — S rimandata, vedi §3.7
// TODO v1.1: storico commenti dopo la risoluzione (audit) — S rimandata, vedi §3.7
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected $fillable = ['content_id', 'author_id', 'author_role', 'body'];

    /** @return BelongsTo<Content, $this> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function authorLabel(): string
    {
        return $this->author_role === 'client' ? 'Cliente' : 'Team';
    }
}
