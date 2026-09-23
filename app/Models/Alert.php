<?php

namespace App\Models;

use App\Enums\AlertType;
use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;

    protected $fillable = ['client_id', 'type', 'subject_type', 'subject_id', 'message', 'resolved_at'];

    protected function casts(): array
    {
        return ['type' => AlertType::class, 'resolved_at' => 'datetime'];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  Builder<Alert>  $query
     * @return Builder<Alert>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Crea l'alert solo se non ne esiste già uno identico aperto per lo stesso soggetto.
     * Senza questo, il comando schedulato genererebbe un duplicato ogni giorno.
     */
    public static function raise(Client $client, AlertType $type, Model $subject, string $message): ?self
    {
        $exists = self::open()
            ->where('client_id', $client->id)
            ->where('type', $type->value)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->exists();

        if ($exists) {
            return null;
        }

        return self::create([
            'client_id' => $client->id,
            'type' => $type->value,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'message' => $message,
        ]);
    }
}
