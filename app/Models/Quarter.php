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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);          // Fase 05
    }

    public function topicPreviews(): HasMany
    {
        return $this->hasMany(TopicPreview::class);      // Fase 08
    }

    /** Calcola label, starts_on ed ends_on da anno e numero: un solo posto, niente duplicazioni. */
    public static function deriveDates(int $year, int $quarterNumber): array
    {
        $start = CarbonImmutable::create($year, ($quarterNumber - 1) * 3 + 1, 1);

        return [
            'label' => "Q{$quarterNumber} {$year}",
            'starts_on' => $start->toDateString(),
            'ends_on' => $start->addMonths(3)->subDay()->toDateString(),
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereDate('starts_on', '<=', today())
            ->whereDate('ends_on', '>=', today());
    }

    public function isCurrent(): bool
    {
        return today()->betweenIncluded($this->starts_on, $this->ends_on);
    }
}
