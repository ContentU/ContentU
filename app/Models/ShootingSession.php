<?php

namespace App\Models;

use App\Enums\ShootingType;
use Database\Factories\ShootingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $session_date
 * @property ShootingType $type
 */
class ShootingSession extends Model
{
    /** @use HasFactory<ShootingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id', 'session_date', 'type', 'is_tentative',
        'checkpoint_required', 'checkpoint_note', 'internal_note',
        'client_approved_at', 'client_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'type' => ShootingType::class,
            'is_tentative' => 'boolean',
            'checkpoint_required' => 'boolean',
            'client_approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<ShootingSessionAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ShootingSessionAssignment::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shooting_session_assignments')
            ->withPivot('role', 'is_alternative');
    }

    /** @return HasMany<ShootingSessionComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(ShootingSessionComment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function clientApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_approved_by');
    }

    /**
     * Solo i campi che il cliente può vedere (decisione B).
     * Usa SEMPRE questo metodo per le viste pubbliche: non costruire l'array a mano.
     *
     * @return array{id: int, date: string, dateLabel: string, type: string, typeLabel: string, isTentative: bool, clientApprovedAt: string|null, comments: array<int, array<string, mixed>>}
     */
    public function toClientArray(): array
    {
        $this->loadMissing('comments.author');

        return [
            'id' => $this->id,
            'date' => $this->session_date->toDateString(),
            'dateLabel' => $this->session_date->translatedFormat('D j M Y'),
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'isTentative' => $this->is_tentative,
            'clientApprovedAt' => $this->client_approved_at?->toIso8601String(),
            'comments' => $this->comments
                ->sortBy('created_at')
                ->values()
                ->map(function (ShootingSessionComment $comment) {
                    $comment->created_at?->locale('it');

                    return [
                        'id' => $comment->id,
                        'authorLabel' => $comment->authorLabel(),
                        'authorName' => $comment->author->name,
                        'body' => $comment->body,
                        'createdAtLabel' => $comment->created_at?->translatedFormat('j M, H:i'),
                    ];
                })->all(),
        ];
    }
}
