<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyMissionProgress extends Model
{
    protected $fillable = [
        'loyalty_mission_id',
        'user_id',
        'period_key',
        'progress_count',
        'completed_at',
        'reward_entry_id',
    ];

    protected $casts = [
        'progress_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMission::class, 'loyalty_mission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
