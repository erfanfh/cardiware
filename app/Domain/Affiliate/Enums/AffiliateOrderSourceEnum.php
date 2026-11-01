<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateOrderSourceEnum: string
{
    case COUPON = 'coupon';
    case REFERRAL = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::COUPON => 'کد تخفیف همکار',
            self::REFERRAL => 'لینک معرفی',
        };
    }
}
