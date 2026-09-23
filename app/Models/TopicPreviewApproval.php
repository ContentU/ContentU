<?php

namespace App\Models;

use Database\Factories\TopicPreviewApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicPreviewApproval extends Model
{
    /** @use HasFactory<TopicPreviewApprovalFactory> */
    use HasFactory;

    protected $fillable = ['topic_preview_id', 'status', 'comment', 'responded_by', 'responded_at'];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function preview(): BelongsTo
    {
        return $this->belongsTo(TopicPreview::class, 'topic_preview_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
