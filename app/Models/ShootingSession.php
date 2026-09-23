<?php

namespace App\Models;

use App\Enums\ShootingType;
use Database\Factories\ShootingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShootingSession extends Model
{
    /** @use HasFactory<ShootingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id', 'session_date', 'type', 'is_tentative',
        'checkpoint_required', 'checkpoint_note', 'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'type' => ShootingType::class,
            'is_tentative' => 'boolean',
            'checkpoint_required' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShootingSessionAssignment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shooting_session_assignments')
            ->withPivot('role', 'is_alternative');
    }

    /**
     * Solo i campi che il cliente può vedere (decisione B).
     * Usa SEMPRE questo metodo per le viste pubbliche: non costruire l'array a mano.
     */
    public function toClientArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->session_date->toDateString(),
            'dateLabel' => $this->session_date->translatedFormat('D j M Y'),
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
        ];
    }
}
