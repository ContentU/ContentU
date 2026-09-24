<?php

namespace App\Models;

use Database\Factories\ShootingSessionCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootingSessionComment extends Model
{
    /** @use HasFactory<ShootingSessionCommentFactory> */
    use HasFactory;

    protected $fillable = ['shooting_session_id', 'author_id', 'author_role', 'body'];

    /** @return BelongsTo<ShootingSession, $this> */
    public function shootingSession(): BelongsTo
    {
        return $this->belongsTo(ShootingSession::class);
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
