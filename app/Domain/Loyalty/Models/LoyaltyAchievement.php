<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LoyaltyAchievement extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'badge',
        'metric',
        'threshold',
        'reward_points',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'metric' => AchievementMetricEnum::class,
        'threshold' => 'integer',
        'reward_points' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'loyalty_achievement_user')
            ->withPivot(['awarded_at', 'reward_entry_id'])
            ->withTimestamps();
    }
}
