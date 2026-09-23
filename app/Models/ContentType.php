<?php

namespace App\Models;

use Database\Factories\ContentTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $key
 * @property string $label
 * @property bool $requires_secondary_asset
 */
class ContentType extends Model
{
    /** @use HasFactory<ContentTypeFactory> */
    use HasFactory;

    protected $fillable = ['key', 'label', 'requires_secondary_asset', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'requires_secondary_asset' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Content, $this> */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * @param  Builder<ContentType>  $query
     * @return Builder<ContentType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
