<?php

namespace App\Models;

use App\Enums\QuarterStatus;
use Carbon\CarbonImmutable;
use Database\Factories\QuarterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property QuarterStatus $status */
class Quarter extends Model
{
    /** @use HasFactory<QuarterFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id', 'year', 'quarter_number', 'label', 'status', 'starts_on', 'ends_on',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuarterStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<Content, $this> */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);          // Fase 05
    }

    /** @return HasMany<TopicPreview, $this> */
    public function topicPreviews(): HasMany
    {
        return $this->hasMany(TopicPreview::class);      // Fase 08
    }

    /**
     * Calcola label, starts_on ed ends_on da anno e numero: un solo posto, niente duplicazioni.
     *
     * @return array{label: string, starts_on: string, ends_on: string}
     */
    public static function deriveDates(int $year, int $quarterNumber): array
    {
        $start = CarbonImmutable::create($year, ($quarterNumber - 1) * 3 + 1, 1);

        return [
            'label' => "Q{$quarterNumber} {$year}",
            'starts_on' => $start->toDateString(),
            'ends_on' => $start->addMonths(3)->subDay()->toDateString(),
        ];
    }

    /**
     * @param  Builder<Quarter>  $query
     * @return Builder<Quarter>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereDate('starts_on', '<=', today())
            ->whereDate('ends_on', '>=', today());
    }

    public function isCurrent(): bool
    {
        return today()->betweenIncluded($this->starts_on, $this->ends_on);
    }

    /** Percentuale di contenuti pronti (programmati o pubblicati) sul totale pianificato del trimestre. */
    public function healthPercentage(): int
    {
        $total = $this->contents()->count();

        if ($total === 0) {
            return 0;
        }

        $ready = $this->contents()->whereIn('status', ['scheduled', 'published'])->count();

        return (int) round($ready / $total * 100);
    }
}
