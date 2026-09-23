<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'brand_name', 'status', 'paused_at', 'contacts',
        'logo_path', 'brand_colors', 'tone_of_voice',
        'internal_notes', 'shooting_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'contacts' => 'array',
            'brand_colors' => 'array',
            'paused_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /** Solo i membri del team assegnati: esclude gli utenti-cliente esterni. */
    public function teamMembers(): BelongsToMany
    {
        return $this->users()->whereIn('role', ['admin', 'account_manager', 'copywriter']);
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);   // Fase 04
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);   // Fase 05
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name));

        return strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
    }
}
