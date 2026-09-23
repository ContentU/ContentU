<?php

namespace App\Models;

use Database\Factories\ClientPublicLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClientPublicLink extends Model
{
    /** @use HasFactory<ClientPublicLinkFactory> */
    use HasFactory;

    protected $fillable = ['client_id', 'token', 'visibility', 'revoked_at'];

    protected function casts(): array
    {
        return ['revoked_at' => 'datetime'];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public static function generateFor(Client $client, string $visibility = 'approved_only'): self
    {
        return self::create([
            'client_id' => $client->id,
            'token' => Str::random(48),
            'visibility' => $visibility,
        ]);
    }

    /**
     * Stati dei contenuti visibili attraverso questo link. Una sola definizione.
     *
     * @return list<string>
     */
    public function visibleStatuses(): array
    {
        return $this->visibility === 'full_ped'
            ? ['approved', 'scheduled', 'published', 'in_review', 'needs_changes']
            : ['approved', 'scheduled', 'published'];
    }
}
