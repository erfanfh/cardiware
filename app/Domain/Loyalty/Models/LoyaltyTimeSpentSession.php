<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\LoyaltyTimeSpentStatusEnum;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTimeSpentSession extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'target_type',
        'target_id',
        'started_at',
        'completed_at',
        'duration_seconds',
        'status',
        'earning_rule_id',
        'rejection_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => LoyaltyTimeSpentStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function earningRule()
    {
        return $this->belongsTo(EarningRule::class);
    }
}
