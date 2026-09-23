<?php

namespace App\Models;

use Database\Factories\TopicPreviewItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicPreviewItem extends Model
{
    /** @use HasFactory<TopicPreviewItemFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_preview_id', 'content_type_id', 'format_label', 'period_label',
        'title', 'theme', 'objective', 'footnote', 'sort_order', 'content_id',
    ];

    /** @return BelongsTo<TopicPreview, $this> */
    public function preview(): BelongsTo
    {
        return $this->belongsTo(TopicPreview::class, 'topic_preview_id');
    }

    /** @return BelongsTo<ContentType, $this> */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    /** @return BelongsTo<Content, $this> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
