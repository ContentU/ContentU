<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'brand_name', 'status', 'paused_at', 'contacts',
        'logo_path', 'brand_colors', 'tone_of_voice',
        'internal_notes', 'shooting_notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (blank($client->slug)) {
                $client->slug = self::uniqueSlug($client->name);
            }
        });
    }

    /** Slug leggibile per il portale pubblico (§3.5): usato nell'URL invece dell'id. */
    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'client';
        $slug = $base;
        $suffix = 2;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'contacts' => 'array',
            'brand_colors' => 'array',
            'paused_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Solo i membri del team assegnati: esclude gli utenti-cliente esterni.
     *
     * @return BelongsToMany<User, $this>
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->users()->whereIn('role', ['admin', 'account_manager', 'copywriter']);
    }

    /** @return HasMany<Quarter, $this> */
    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);   // Fase 04
    }

    /** @return HasMany<Tag, $this> */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);   // Fase 05
    }

    /** @return HasMany<ClientPublicLink, $this> */
    public function publicLinks(): HasMany
    {
        return $this->hasMany(ClientPublicLink::class);   // Fase 07
    }

    /** @return HasMany<ShootingTarget, $this> */
    public function shootingTargets(): HasMany
    {
        return $this->hasMany(ShootingTarget::class);   // Fase 12
    }

    /** @return HasMany<ShootingSession, $this> */
    public function shootingSessions(): HasMany
    {
        return $this->hasMany(ShootingSession::class);   // Fase 12
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name));

        return strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
    }
}
