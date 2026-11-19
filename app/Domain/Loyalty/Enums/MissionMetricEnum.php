<?php

namespace App\Domain\Loyalty\Enums;

enum MissionMetricEnum: string
{
    case DAILY_VISITS = 'daily_visits';
    case APPROVED_COMMENTS = 'approved_comments';
    case PAID_ORDERS = 'paid_orders';
    case APPROVED_MEDIA_SUBMISSIONS = 'approved_media_submissions';
    case REFERRAL_SIGNUPS = 'referral_signups';

    public function label(): string
    {
        return match ($this) {
            self::DAILY_VISITS => 'روز بازدید از سایت',
            self::APPROVED_COMMENTS => 'دیدگاه تأییدشده',
            self::PAID_ORDERS => 'سفارش پرداخت‌شده',
            self::APPROVED_MEDIA_SUBMISSIONS => 'رسانه تأییدشده (تصویر/ویدیو)',
            self::REFERRAL_SIGNUPS => 'دعوت دوست (ثبت‌نام با لینک شما)',
        };
    }
}
