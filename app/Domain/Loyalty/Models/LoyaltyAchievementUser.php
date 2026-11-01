<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyAchievementUser extends Model
{
    protected $fillable = [
        'loyalty_achievement_id',
        'user_id',
        'awarded_at',
        'reward_entry_id',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAchievement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Identity\Models\User::class);
    }
}
