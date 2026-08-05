<?php

namespace App\Application\Commerce\Analytics\Enums;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;

enum AnalyticsGranularityEnum: string
{
    case AUTO = 'auto';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    public function label(): string
    {
        return match ($this) {
            self::AUTO => 'خودکار',
            self::HOUR => 'ساعتی',
            self::DAY => 'روزانه',
            self::WEEK => 'هفتگی',
            self::MONTH => 'ماهانه',
        };
    }

    public function resolveFor(AnalyticsDateRangeDTO $range): self
    {
        $hours = max(1, (int)ceil($range->from->diffInHours($range->to)));
        $days = max(1, (int)ceil($range->from->diffInDays($range->to)));

        if ($this === self::AUTO) {
            return match (true) {
                $hours <= 48 => self::HOUR,
                $days <= 90 => self::DAY,
                $days <= 730 => self::WEEK,
                default => self::MONTH,
            };
        }

        if ($this === self::HOUR && $days > 31) {
            return self::DAY;
        }

        if ($this === self::DAY && $days > 370) {
            return self::WEEK;
        }

        if ($this === self::WEEK && $days > 1095) {
            return self::MONTH;
        }

        return $this;
    }
}
