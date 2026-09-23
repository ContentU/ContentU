<?php

namespace App\Models;

use Database\Factories\ShootingTargetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootingTarget extends Model
{
    /** @use HasFactory<ShootingTargetFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id', 'quarter_id', 'period_label',
        'ideal_sessions', 'planned_sessions', 'potential_sessions',
        'weight', 'status_note',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }
}
