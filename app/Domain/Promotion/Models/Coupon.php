<?php

namespace App\Domain\Promotion\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'value',
        'max_discount_amount',
        'scope',
        'status',
        'applies_to_all_users',
        'starts_at',
        'expires_at',
        'minimum_order_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'is_floating_box_enabled',
        'applies_to_addons',
        'created_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'type' => CouponTypeEnum::class,
        'scope' => CouponScopeEnum::class,
        'status' => CouponStatusEnum::class,
        'applies_to_all_users' => 'boolean',
        'applies_to_addons' => 'boolean',
        'value' => 'integer',
        'max_discount_amount' => 'integer',
        'minimum_order_amount' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
        'is_floating_box_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'coupon_users',
            'coupon_id',
            'user_id'
        )->withTimestamps();
    }

    public function couponUsers(): HasMany
    {
        return $this->hasMany(CouponUser::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(CouponTarget::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isCurrentlyUsable(): bool
    {
        return $this->isActive()
            && $this->hasStarted()
            && !$this->isExpired();
    }

    public function hasUsageLimit(): bool
    {
        return $this->usage_limit !== null;
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->hasUsageLimit()
            && $this->used_count >= $this->usage_limit;
    }

    public function appliesToAllUsers(): bool
    {
        return $this->applies_to_all_users;
    }

    public function isLimitedToSpecificUsers(): bool
    {
        return !$this->applies_to_all_users;
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(CouponReservation::class);
    }
}
