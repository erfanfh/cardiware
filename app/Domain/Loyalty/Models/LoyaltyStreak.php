<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyStreak extends Model
{
    protected $fillable = [
        'user_id',
        'current_days',
        'longest_days',
        'last_visit_date',
        'current_streak_started_on',
    ];

    protected $casts = [
        'current_days' => 'integer',
        'longest_days' => 'integer',
        'last_visit_date' => 'date',
        'current_streak_started_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
