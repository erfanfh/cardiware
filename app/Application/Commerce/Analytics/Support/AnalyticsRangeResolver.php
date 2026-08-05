<?php

namespace App\Application\Commerce\Analytics\Support;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;
use Carbon\CarbonImmutable;
use Morilog\Jalali\Jalalian;

class AnalyticsRangeResolver
{
    public function resolveFilter(
        AnalyticsRangePresetEnum $preset,
        AnalyticsComparisonEnum  $comparison,
    ): AnalyticsFilterDTO
    {
        $range = $this->resolveRange($preset);

        return new AnalyticsFilterDTO(
            preset: $preset,
            range: $range,
            comparison: $comparison,
            comparisonRange: $comparison === AnalyticsComparisonEnum::PREVIOUS_PERIOD
                ? $this->previousPeriod($range)
                : null,
        );
    }

    public function resolveRange(
        AnalyticsRangePresetEnum $preset,
        ?CarbonImmutable         $now = null,
    ): AnalyticsDateRangeDTO
    {
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
        };

        return new AnalyticsDateRangeDTO(
            from: $from,
            to: $to,
            label: $preset->label(),
        );
    }

    public function previousPeriod(
        AnalyticsDateRangeDTO $range,
    ): AnalyticsDateRangeDTO
    {
        $durationInSeconds = max(
            1,
            $range->from->diffInSeconds($range->to) + 1,
        );

        $to = $range->from->subSecond();
        $from = $to->subSeconds($durationInSeconds - 1);

        return new AnalyticsDateRangeDTO(
            from: $from,
            to: $to,
            label: 'دوره قبل',
        );
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
