<?php

namespace App\Domain\Promotion\Enums;

enum CouponScopeEnum: string
{
    case ORDER = 'order';
    case PRODUCTS = 'products';
    case STORE = 'store';
    case VIP = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::ORDER => 'کل سفارش',
            self::PRODUCTS => 'محصولات خاص',
            self::STORE => 'فروشنده خاص',
            self::VIP => 'اشتراک‌های VIP',
        };
    }

    public function isOrder(): bool
    {
        return $this === self::ORDER;
    }

    public function isProducts(): bool
    {
        return $this === self::PRODUCTS;
    }

    public function isStore(): bool
    {
        return $this === self::STORE;
    }
}
