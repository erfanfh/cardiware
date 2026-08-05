<?php

namespace App\Application\Commerce\Analytics\Enums;

enum AnalyticsRangePresetEnum: string
{
    case TODAY = 'today';
    case YESTERDAY = 'yesterday';
    case LAST_7_DAYS = 'last_7_days';
    case LAST_30_DAYS = 'last_30_days';
    case CURRENT_MONTH = 'current_month';
    case PREVIOUS_MONTH = 'previous_month';
    case CURRENT_YEAR = 'current_year';
    case CUSTOM = 'custom';

    public function cacheTtlSeconds(): int
    {
        return match ($this) {
            self::TODAY => 45,
            self::YESTERDAY => 600,
            self::LAST_7_DAYS => 120,
            self::LAST_30_DAYS => 300,
            self::CURRENT_MONTH => 300,
            self::PREVIOUS_MONTH => 1800,
            self::CURRENT_YEAR => 600,
            self::CUSTOM => 300,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::TODAY => 'امروز',
            self::YESTERDAY => 'دیروز',
            self::LAST_7_DAYS => '۷ روز اخیر',
            self::LAST_30_DAYS => '۳۰ روز اخیر',
            self::CURRENT_MONTH => 'ماه جاری',
            self::PREVIOUS_MONTH => 'ماه قبل',
            self::CURRENT_YEAR => 'سال جاری',
            self::CUSTOM => 'بازه سفارشی',
        };
    }
}
