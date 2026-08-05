<?php

namespace App\Application\Commerce\Analytics\Support;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Morilog\Jalali\Jalalian;
use Throwable;

class AnalyticsRangeResolver
{
    private const MAX_CUSTOM_RANGE_DAYS = 1096;

    public function resolveFilter(
        AnalyticsRangePresetEnum $preset,
        AnalyticsComparisonEnum $comparison,
        AnalyticsGranularityEnum $granularity = AnalyticsGranularityEnum::AUTO,
        ?string $customFrom = null,
        ?string $customTo = null,
    ): AnalyticsFilterDTO {
        $range = $preset === AnalyticsRangePresetEnum::CUSTOM
            ? $this->resolveCustomRange((string)$customFrom, (string)$customTo)
            : $this->resolveRange($preset);

        $resolvedGranularity = $granularity->resolveFor($range);

        return new AnalyticsFilterDTO(
            preset: $preset,
            range: $range,
            comparison: $comparison,
            comparisonRange: $comparison === AnalyticsComparisonEnum::PREVIOUS_PERIOD
                ? $this->previousPeriod($range)
                : null,
            requestedGranularity: $granularity,
            granularity: $resolvedGranularity,
        );
    }

    public function resolveRange(
        AnalyticsRangePresetEnum $preset,
        ?CarbonImmutable $now = null,
    ): AnalyticsDateRangeDTO {
        if ($preset === AnalyticsRangePresetEnum::CUSTOM) {
            throw new InvalidArgumentException('برای بازه سفارشی باید تاریخ شروع و پایان مشخص شود.');
        }

        $now ??= now()->toImmutable();
        $today = $now->startOfDay();

        [$from, $to] = match ($preset) {
            AnalyticsRangePresetEnum::TODAY => [
                $today,
                $now,
            ],
            AnalyticsRangePresetEnum::YESTERDAY => [
                $today->subDay(),
                $today->subDay()->endOfDay(),
            ],
            AnalyticsRangePresetEnum::LAST_7_DAYS => [
                $today->subDays(6),
                $now,
            ],
            AnalyticsRangePresetEnum::LAST_30_DAYS => [
                $today->subDays(29),
                $now,
            ],
            AnalyticsRangePresetEnum::CURRENT_MONTH => [
                $this->jalaliMonthStart($now),
                $now,
            ],
            AnalyticsRangePresetEnum::PREVIOUS_MONTH => [
                $this->jalaliPreviousMonthStart($now),
                $this->jalaliMonthStart($now)->subSecond(),
            ],
            AnalyticsRangePresetEnum::CURRENT_YEAR => [
                $this->jalaliYearStart($now),
                $now,
            ],
            AnalyticsRangePresetEnum::CUSTOM => throw new InvalidArgumentException(
                'برای بازه سفارشی باید تاریخ شروع و پایان مشخص شود.',
            ),
        };

        return new AnalyticsDateRangeDTO(
            from: $from,
            to: $to,
            label: $preset->label(),
        );
    }

    public function resolveCustomRange(string $from, string $to): AnalyticsDateRangeDTO
    {
        $fromDate = $this->parseJalaliDate($from, 'تاریخ شروع');
        $toDate = $this->parseJalaliDate($to, 'تاریخ پایان');

        if ($toDate->lessThan($fromDate)) {
            throw new InvalidArgumentException('تاریخ پایان نمیتواند قبل از تاریخ شروع باشد.');
        }

        $days = (int)floor($fromDate->diffInDays($toDate));

        if ($days > self::MAX_CUSTOM_RANGE_DAYS) {
            throw new InvalidArgumentException('حداکثر بازه سفارشی قابل گزارش سه سال است.');
        }

        return new AnalyticsDateRangeDTO(
            from: $fromDate->startOfDay(),
            to: $toDate->endOfDay(),
            label: 'بازه سفارشی',
        );
    }

    public function formatJalaliDate(CarbonImmutable $date): string
    {
        return Jalalian::fromCarbon($date->toMutable())->format('Y/m/d');
    }

    public function previousPeriod(AnalyticsDateRangeDTO $range): AnalyticsDateRangeDTO
    {
        $durationInSeconds = max(
            1,
            (int)floor($range->from->diffInSeconds($range->to)) + 1,
        );

        $to = $range->from->subSecond();
        $from = $to->subSeconds($durationInSeconds - 1);

        return new AnalyticsDateRangeDTO(
            from: $from,
            to: $to,
            label: 'دوره قبل',
        );
    }

    private function parseJalaliDate(string $value, string $fieldLabel): CarbonImmutable
    {
        $normalized = trim($this->normalizeDigits($value));

        if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $normalized, $matches)) {
            throw new InvalidArgumentException($fieldLabel . ' باید با فرمت ۱۴۰۵/۰۵/۱۴ وارد شود.');
        }

        try {
            return (new Jalalian(
                (int)$matches[1],
                (int)$matches[2],
                (int)$matches[3],
            ))
                ->toCarbon()
                ->toImmutable();
        } catch (Throwable) {
            throw new InvalidArgumentException($fieldLabel . ' معتبر نیست.');
        }
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }

    private function jalaliMonthStart(CarbonImmutable $date): CarbonImmutable
    {
        return Jalalian::fromCarbon($date->toMutable())
            ->getFirstDayOfMonth()
            ->toCarbon()
            ->startOfDay()
            ->toImmutable();
    }

    private function jalaliPreviousMonthStart(CarbonImmutable $date): CarbonImmutable
    {
        $currentMonthStart = $this->jalaliMonthStart($date);
        $previousDay = $currentMonthStart->subDay();

        return Jalalian::fromCarbon($previousDay->toMutable())
            ->getFirstDayOfMonth()
            ->toCarbon()
            ->startOfDay()
            ->toImmutable();
    }

    private function jalaliYearStart(CarbonImmutable $date): CarbonImmutable
    {
        $jalali = Jalalian::fromCarbon($date->toMutable());

        return (new Jalalian($jalali->getYear(), 1, 1))
            ->toCarbon()
            ->startOfDay()
            ->toImmutable();
    }
}
