<?php

namespace App\Models;

use App\Enums\ShootingRole;
use Database\Factories\ShootingSessionAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShootingSessionAssignment extends Model
{
    /** @use HasFactory<ShootingSessionAssignmentFactory> */
    use HasFactory;

    protected $fillable = ['shooting_session_id', 'user_id', 'role', 'is_alternative'];

    protected function casts(): array
    {
        return [
            'role' => ShootingRole::class,
            'is_alternative' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ShootingSession::class, 'shooting_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
