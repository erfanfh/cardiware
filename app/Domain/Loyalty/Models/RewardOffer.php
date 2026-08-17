<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

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
        'config',
        'target_type',
        'target_id',
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

    protected static function booted(): void
    {
        static::creating(function (RewardOffer $offer): void {
            if ((int) $offer->points_cost <= 0) {
                throw new LogicException('Reward points cost must be greater than zero.');
            }
        });
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return !$this->ends_at || !$this->ends_at->isPast();
    }

    public function hasReachedGlobalLimit(): bool
    {
        return $this->redemption_limit !== null
            && (int) $this->redeemed_count >= (int) $this->redemption_limit;
    }

    public function remainingRedemptions(): ?int
    {
        if ($this->redemption_limit === null) {
            return null;
        }

        return max(0, (int) $this->redemption_limit - (int) $this->redeemed_count);
    }
}
