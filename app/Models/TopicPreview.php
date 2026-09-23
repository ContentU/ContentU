<?php

namespace App\Models;

use Database\Factories\TopicPreviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TopicPreview extends Model
{
    /** @use HasFactory<TopicPreviewFactory> */
    use HasFactory;

    protected $fillable = ['quarter_id', 'month_label', 'month_order', 'status', 'note'];

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TopicPreviewItem::class)->orderBy('sort_order');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TopicPreviewApproval::class)->latest('responded_at');
    }

    public function latestApproval(): HasOne
    {
        return $this->hasOne(TopicPreviewApproval::class)->latestOfMany('responded_at');
    }

    public function statusLabel(): string
    {
        return $this->status === 'ready' ? 'Proposta pronta' : 'Roadmap da confermare';
    }
}
