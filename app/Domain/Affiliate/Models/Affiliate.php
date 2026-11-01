<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateCommissionTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'is_active',
        'commission_type',
        'commission_value',
        'cookie_days',
        'attribution_days',
        'min_withdrawal_amount',
        'starts_at',
        'ends_at',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_type' => AffiliateCommissionTypeEnum::class,
        'commission_value' => 'float',
        'cookie_days' => 'integer',
        'attribution_days' => 'integer',
        'min_withdrawal_amount' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(AffiliateOrder::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /** همکاری که فعال باشد و در بازه زمانی مجاز باشد. */
    public function scopeEligible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /** محاسبه کمیسیون بر اساس مبلغ سفارش (تومان). */
    public function commissionFor(int $orderAmount): int
    {
        if ($orderAmount <= 0) {
            return 0;
        }

        $commission = $this->commission_type === AffiliateCommissionTypeEnum::PERCENT
            ? (int) floor($orderAmount * $this->commission_value / 100)
            : (int) floor($this->commission_value);

        // کمیسیون ثابت هرگز از مبلغ خود سفارش بیشتر نمی‌شود.
        return max(0, min($commission, $orderAmount));
    }
}
