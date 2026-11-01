<?php

namespace App\Domain\Loyalty\Enums;

enum AchievementMetricEnum: string
{
    case PAID_ORDERS_COUNT = 'paid_orders_count';
    case APPROVED_COMMENTS_COUNT = 'approved_comments_count';
    case LONGEST_STREAK_DAYS = 'longest_streak_days';
    case LIFETIME_XP = 'lifetime_xp';
    case MEMBERSHIP_DAYS = 'membership_days';
    case TOTAL_PURCHASE_AMOUNT = 'total_purchase_amount';

    public function label(): string
    {
        return match ($this) {
            self::PAID_ORDERS_COUNT => 'تعداد سفارش‌های پرداخت‌شده',
            self::APPROVED_COMMENTS_COUNT => 'تعداد دیدگاه‌های تأییدشده',
            self::LONGEST_STREAK_DAYS => 'طولانی‌ترین زنجیره ورود روزانه',
            self::LIFETIME_XP => 'کل XP',
            self::MEMBERSHIP_DAYS => 'روز عضویت',
            self::TOTAL_PURCHASE_AMOUNT => 'مجموع مبلغ خریدها (تومان)',
        };
    }
}
