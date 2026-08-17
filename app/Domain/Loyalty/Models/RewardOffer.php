<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RewardOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'points_cost',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
        'redemption_limit',
        'redemption_limit_per_user',
        'redeemed_count',
        'config',
    ];

    protected $casts = [
        'type' => RewardTypeEnum::class,
        'points_cost' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'redemption_limit' => 'integer',
        'redemption_limit_per_user' => 'integer',
        'redeemed_count' => 'integer',
        'config' => 'array',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn(Builder $query) => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now))
            ->where(fn(Builder $query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $now));
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->lte(now());
    }

    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->lt(now());
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active && $this->hasStarted() && !$this->hasEnded();
    }

    public function hasReachedGlobalLimit(): bool
    {
        return $this->redemption_limit !== null
            && (int)$this->redeemed_count >= (int)$this->redemption_limit;
    }

    public function remainingRedemptions(): ?int
    {
        if ($this->redemption_limit === null) {
            return null;
        }

        return max(0, (int)$this->redemption_limit - (int)$this->redeemed_count);
    }
}
