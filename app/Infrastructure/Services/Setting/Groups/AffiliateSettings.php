<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class AffiliateSettings extends BaseSettings
{
    protected string $group = 'affiliate';

    protected array $allowedKeys = [
        'affiliate_enabled',
        'default_commission_type',
        'default_commission_value',
        'default_attribution_days',
        'default_cookie_days',
        'default_min_withdrawal_amount',
        'referral_bonus_enabled',
        'referral_bonus_referrer_points',
        'referral_bonus_referee_points',
        'referral_bonus_min_order_amount',
    ];

    protected array $casts = [
        'affiliate_enabled' => BooleanCast::class,
        'default_commission_value' => IntegerCast::class,
        'default_attribution_days' => IntegerCast::class,
        'default_cookie_days' => IntegerCast::class,
        'default_min_withdrawal_amount' => IntegerCast::class,
        'referral_bonus_enabled' => BooleanCast::class,
        'referral_bonus_referrer_points' => IntegerCast::class,
        'referral_bonus_referee_points' => IntegerCast::class,
        'referral_bonus_min_order_amount' => IntegerCast::class,
    ];

    public function isEnabled(): bool
    {
        return (bool) $this->get('affiliate_enabled', true);
    }

    public function defaultCommissionType(): string
    {
        return (string) $this->get('default_commission_type', 'percent');
    }

    public function defaultCommissionValue(): int
    {
        return (int) $this->get('default_commission_value', 11);
    }

    public function defaultAttributionDays(): int
    {
        return max(1, (int) $this->get('default_attribution_days', 90));
    }

    public function defaultCookieDays(): int
    {
        return max(1, (int) $this->get('default_cookie_days', 30));
    }

    public function defaultMinWithdrawalAmount(): int
    {
        return max(0, (int) $this->get('default_min_withdrawal_amount', 100000));
    }

    public function referralBonusEnabled(): bool
    {
        return (bool) $this->get('referral_bonus_enabled', true);
    }

    public function referralBonusReferrerPoints(): int
    {
        return max(0, (int) $this->get('referral_bonus_referrer_points', 1000));
    }

    public function referralBonusRefereePoints(): int
    {
        return max(0, (int) $this->get('referral_bonus_referee_points', 500));
    }

    public function referralBonusMinOrderAmount(): int
    {
        return max(0, (int) $this->get('referral_bonus_min_order_amount', 100000));
    }
}
