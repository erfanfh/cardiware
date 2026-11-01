<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyMission extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'icon',
        'metric',
        'target_count',
        'reward_points',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'metric' => MissionMetricEnum::class,
        'target_count' => 'integer',
        'reward_points' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(LoyaltyMissionProgress::class);
    }

    public function progressFor(User $user, string $periodKey): ?LoyaltyMissionProgress
    {
        return $this->progressEntries()
            ->where('user_id', $user->getKey())
            ->where('period_key', $periodKey)
            ->first();
    }
}
