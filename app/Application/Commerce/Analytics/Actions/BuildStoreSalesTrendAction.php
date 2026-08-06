<?php

namespace App\Application\Commerce\Analytics\Actions;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class BuildStoreSalesTrendAction
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function execute(
        int $storeId,
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        $buckets = $this->trendBuckets($range, $granularity);

        if ($buckets->isEmpty()) {
            return collect();
        }

        $sourceRows = $this->sourceRows($storeId, $range, $granularity);
        $bucketLookup = $this->bucketLookup($buckets, $granularity);

        $result = $buckets
            ->map(static fn(array $bucket): array => [
                'date' => $bucket['from']->toISOString(),
                'label' => $bucket['label'],
                'period_label' => $bucket['period_label'],
                'orders_count' => 0,
                'gross_sales' => 0,
                'discount_amount' => 0,
                'net_sales' => 0,
            ])
            ->values();

        foreach ($sourceRows as $row) {
            $index = $bucketLookup->get((string)$row->bucket);

            if ($index === null) {
                continue;
            }

            $current = $result->get($index);

            $current['orders_count'] += (int)$row->orders_count;
            $current['gross_sales'] += (int)$row->gross_sales;
            $current['discount_amount'] += (int)$row->discount_amount;
            $current['net_sales'] += (int)$row->net_sales;

            $result->put($index, $current);
        }

        return $result->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $current
     * @param Collection<int, array<string, mixed>>|null $comparison
     * @return Collection<int, array<string, mixed>>
     */
    public function mergeComparison(
        Collection $current,
        ?Collection $comparison,
    ): Collection {
        return $current
            ->values()
            ->map(function (array $row, int $index) use ($comparison): array {
                $comparisonRow = $comparison?->values()->get($index);

                return [
                    ...$row,
                    'comparison_label' => $comparisonRow['period_label'] ?? null,
                    'comparison_orders_count' => $comparisonRow['orders_count'] ?? null,
                    'comparison_gross_sales' => $comparisonRow['gross_sales'] ?? null,
                    'comparison_discount_amount' => $comparisonRow['discount_amount'] ?? null,
                    'comparison_net_sales' => $comparisonRow['net_sales'] ?? null,
                ];
            });
    }

    /**
     * @return Collection<int, object>
     */
    private function sourceRows(
        int $storeId,
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        $bucketExpression = $granularity === AnalyticsGranularityEnum::HOUR
            ? "DATE_FORMAT(orders.paid_at, '%Y-%m-%d %H:00:00')"
            : 'DATE(orders.paid_at)';

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.store_id', $storeId)
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->whereNotNull('orders.paid_at')
            ->whereBetween('orders.paid_at', [$range->from, $range->to])
            ->groupByRaw($bucketExpression)
            ->orderByRaw($bucketExpression)
            ->selectRaw($bucketExpression . ' as bucket')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('COALESCE(SUM(order_items.subtotal_amount), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(order_items.discount_amount + order_items.coupon_discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(order_items.final_total_amount), 0) as net_sales')
            ->get();
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function trendBuckets(
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        return match ($granularity) {
            AnalyticsGranularityEnum::HOUR => $this->hourlyBuckets($range),
            AnalyticsGranularityEnum::DAY => $this->dailyBuckets($range),
            AnalyticsGranularityEnum::WEEK => $this->weeklyBuckets($range),
            AnalyticsGranularityEnum::MONTH => $this->monthlyBuckets($range),
            AnalyticsGranularityEnum::AUTO => collect(),
        };
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function hourlyBuckets(AnalyticsDateRangeDTO $range): Collection
    {
        $cursor = $range->from->startOfHour();
        $lastHour = $range->to->startOfHour();
        $multipleDays = !$range->from->startOfDay()->equalTo($range->to->startOfDay());
        $result = collect();

        while ($cursor->lessThanOrEqualTo($lastHour)) {
            $to = $cursor->endOfHour()->lessThan($range->to)
                ? $cursor->endOfHour()
                : $range->to;
            $jalali = Jalalian::fromCarbon($cursor->toMutable());

            $result->push([
                'from' => $cursor,
                'to' => $to,
                'label' => $multipleDays
                    ? $jalali->format('m/d') . ' ' . $cursor->format('H')
                    : $cursor->format('H:i'),
                'period_label' => $jalali->format('Y/m/d') . ' ساعت ' . $cursor->format('H:00'),
            ]);

            $cursor = $cursor->addHour();
        }

        return $result;
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function dailyBuckets(AnalyticsDateRangeDTO $range): Collection
    {
        $cursor = $range->from->startOfDay();
        $lastDay = $range->to->startOfDay();
        $showYear = $range->from->diffInDays($range->to) > 180;
        $result = collect();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            $to = $cursor->endOfDay()->lessThan($range->to)
                ? $cursor->endOfDay()
                : $range->to;
            $jalali = Jalalian::fromCarbon($cursor->toMutable());

            $result->push([
                'from' => $cursor,
                'to' => $to,
                'label' => $jalali->format($showYear ? 'Y/m/d' : 'm/d'),
                'period_label' => $jalali->format('Y/m/d'),
            ]);

            $cursor = $cursor->addDay();
        }

        return $result;
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function weeklyBuckets(AnalyticsDateRangeDTO $range): Collection
    {
        $cursor = $range->from->startOfDay();
        $result = collect();

        while ($cursor->lessThanOrEqualTo($range->to)) {
            $from = $cursor->lessThan($range->from) ? $range->from : $cursor;
            $candidateTo = $cursor->addDays(6)->endOfDay();
            $to = $candidateTo->lessThan($range->to) ? $candidateTo : $range->to;
            $fromJalali = Jalalian::fromCarbon($from->toMutable());
            $toJalali = Jalalian::fromCarbon($to->toMutable());

            $result->push([
                'from' => $from,
                'to' => $to,
                'label' => $fromJalali->format('m/d') . ' تا ' . $toJalali->format('m/d'),
                'period_label' => $fromJalali->format('Y/m/d') . ' تا ' . $toJalali->format('Y/m/d'),
            ]);

            $cursor = $cursor->addDays(7);
        }

        return $result;
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function monthlyBuckets(AnalyticsDateRangeDTO $range): Collection
    {
        $cursor = $this->jalaliMonthStart($range->from);
        $result = collect();

        while ($cursor->lessThanOrEqualTo($range->to)) {
            $nextMonth = $this->jalaliNextMonthStart($cursor);
            $from = $cursor->lessThan($range->from) ? $range->from : $cursor;
            $candidateTo = $nextMonth->subSecond();
            $to = $candidateTo->lessThan($range->to) ? $candidateTo : $range->to;
            $jalali = Jalalian::fromCarbon($cursor->toMutable());

            $result->push([
                'from' => $from,
                'to' => $to,
                'label' => $jalali->format('Y/m'),
                'period_label' => $jalali->format('Y/m'),
            ]);

            $cursor = $nextMonth;
        }

        return $result;
    }

    /**
     * @param Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}> $buckets
     * @return Collection<string, int>
     */
    private function bucketLookup(
        Collection $buckets,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        $lookup = collect();
        $hourly = $granularity === AnalyticsGranularityEnum::HOUR;

        foreach ($buckets as $index => $bucket) {
            $cursor = $hourly
                ? $bucket['from']->startOfHour()
                : $bucket['from']->startOfDay();
            $last = $hourly
                ? $bucket['to']->startOfHour()
                : $bucket['to']->startOfDay();

            while ($cursor->lessThanOrEqualTo($last)) {
                $lookup->put(
                    $hourly
                        ? $cursor->format('Y-m-d H:00:00')
                        : $cursor->format('Y-m-d'),
                    $index,
                );

                $cursor = $hourly
                    ? $cursor->addHour()
                    : $cursor->addDay();
            }
        }

        return $lookup;
    }

    private function jalaliMonthStart(CarbonImmutable $date): CarbonImmutable
    {
        return Jalalian::fromCarbon($date->toMutable())
            ->getFirstDayOfMonth()
            ->toCarbon()
            ->startOfDay()
            ->toImmutable();
    }

    private function jalaliNextMonthStart(CarbonImmutable $date): CarbonImmutable
    {
        $jalali = Jalalian::fromCarbon($date->toMutable());
        $year = (int)$jalali->format('Y');
        $month = (int)$jalali->format('m');

        if ($month === 12) {
            $year++;
            $month = 1;
        } else {
            $month++;
        }

        return (new Jalalian($year, $month, 1))
            ->toCarbon()
            ->startOfDay()
            ->toImmutable();
    }
}
