<?php

namespace App\Application\Commerce\Analytics\Enums;

enum AnalyticsComparisonEnum: string
{
    case NONE = 'none';
    case PREVIOUS_PERIOD = 'previous_period';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'بدون مقایسه',
            self::PREVIOUS_PERIOD => 'دوره قبل',
        };
    }
}
