<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    protected $fillable = [
        'quarter_id', 'content_type_id', 'title', 'caption', 'hashtags',
        'resource_url', 'cover_resource_url', 'publish_at', 'status', 'channels',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'publish_at' => 'datetime',
            'channels' => 'array',
        ];
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Cosa manca al contenuto per poter essere programmato.
     * Usato dalla checklist (05.10) e dagli alert (Fase 10): una sola definizione.
     *
     * @return array{blocking: string[], warnings: string[]}
     */
    public function readiness(): array
    {
        $blocking = [];
        $warnings = [];

        if (blank($this->resource_url)) {
            $blocking[] = 'Manca il link alla risorsa.';
        }

        if ($this->contentType?->requires_secondary_asset && blank($this->cover_resource_url)) {
            $blocking[] = "La tipologia «{$this->contentType->label}» richiede anche la cover: manca il link alla cover.";
        }

        if (blank($this->caption)) {
            $blocking[] = 'Manca la caption.';
        }

        // Hashtag e tag sono un AVVISO, non un blocco: coerente con l'alert §3.8
        // che li tratta come segnalazione.
        if (blank($this->hashtags)) {
            $warnings[] = 'Nessun hashtag.';
        }

        if ($this->tags()->count() === 0) {
            $warnings[] = 'Nessun tag interno.';
        }

        if (blank($this->channels)) {
            $warnings[] = 'Nessun canale selezionato.';
        }

        return ['blocking' => $blocking, 'warnings' => $warnings];
    }

    public function isReadyToSchedule(): bool
    {
        return $this->readiness()['blocking'] === [];
    }

    /**
     * Un solo data layer per il feed (griglia/elenco), usato sia dall'area
     * interna (Fase 06) sia dal portale pubblico del cliente (Fase 07).
     *
     * @return array<string, mixed>
     */
    public function toFeedArray(): array
    {
        $this->loadMissing('contentType', 'tags');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'caption' => $this->caption,
            'hashtags' => $this->hashtags,
            'resourceUrl' => $this->resource_url,
            'coverResourceUrl' => $this->cover_resource_url,
            'publishAt' => $this->publish_at->toIso8601String(),
            'publishAtLabel' => $this->publish_at->locale('it')->translatedFormat('D j M'),
            'status' => $this->status->value,
            'typeLabel' => mb_strtoupper($this->contentType->label),
            'typeKey' => $this->contentType->key,
            'channels' => $this->channels ?? [],
            'tags' => $this->tags->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'label' => $tag->label,
            ])->all(),
            'isToday' => $this->publish_at->isToday(),
        ];
    }
}
