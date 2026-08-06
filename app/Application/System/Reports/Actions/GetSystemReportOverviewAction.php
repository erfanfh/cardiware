<?php

namespace App\Application\System\Reports\Actions;

use App\Application\Commerce\Analytics\DTOs\AnalyticsDateRangeDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsMetricDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum;
use App\Application\System\Reports\DTOs\SystemReportOverviewDTO;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class GetSystemReportOverviewAction
{
    public function execute(AnalyticsFilterDTO $filter): SystemReportOverviewDTO
    {
        return Cache::remember(
            $this->cacheKey($filter),
            now()->addSeconds($filter->preset->cacheTtlSeconds()),
            fn(): SystemReportOverviewDTO => $this->buildOverview($filter),
        );
    }

    public function forget(AnalyticsFilterDTO $filter): void
    {
        Cache::forget($this->cacheKey($filter));
    }

    private function buildOverview(AnalyticsFilterDTO $filter): SystemReportOverviewDTO
    {
        $current = $this->summary($filter->range);
        $previous = $filter->comparisonRange
            ? $this->summary($filter->comparisonRange)
            : null;

        $currentTrend = $this->trend(
            range: $filter->range,
            granularity: $filter->granularity,
        );

        $comparisonTrend = $filter->comparisonRange
            ? $this->trend(
                range: $filter->comparisonRange,
                granularity: $filter->granularity,
            )
            : null;

        return new SystemReportOverviewDTO(
            filter: $filter,
            metrics: $this->buildMetrics($current, $previous),
            trend: $this->mergeTrendComparison($currentTrend, $comparisonTrend),
            otpTypes: $this->otpTypePerformance($filter->range),
            topDownloadedProducts: $this->topDownloadedProducts($filter->range),
            systemSnapshot: $this->systemSnapshot(),
            commentStatuses: $this->commentStatusBreakdown($filter->range),
            ticketStatuses: $this->ticketStatusBreakdown($filter->range),
            generatedAt: now()->toImmutable(),
        );
    }

    private function cacheKey(AnalyticsFilterDTO $filter): string
    {
        return 'system-reports:overview:v2:' . $filter->cacheKey();
    }

    /**
     * @return array<string, int|float>
     */
    private function summary(AnalyticsDateRangeDTO $range): array
    {
        $users = DB::table('users')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as new_users')
            ->selectRaw('COALESCE(SUM(CASE WHEN mobile_verified_at IS NOT NULL THEN 1 ELSE 0 END), 0) as verified_new_users')
            ->first();

        $otpMessages = DB::table('otps')
            ->where('key_type', 'mobile')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as otp_messages')
            ->selectRaw('COUNT(DISTINCT `key`) as unique_otp_recipients')
            ->selectRaw('COALESCE(SUM(attempts), 0) as otp_validation_attempts')
            ->selectRaw('COALESCE(SUM(CASE WHEN attempts > 0 THEN 1 ELSE 0 END), 0) as otp_with_validation_errors')
            ->first();

        $comments = DB::table('comments')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as new_comments')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as approved_comments',
                [CommentStatusEnum::APPROVED->value],
            )
            ->first();

        $tickets = DB::table('tickets')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->count();

        $closedTickets = DB::table('tickets')
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$range->from, $range->to])
            ->count();

        $downloads = DB::table('purchase_access_downloads')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->selectRaw('COUNT(*) as download_sessions')
            ->selectRaw('COUNT(DISTINCT user_id) as unique_downloaders')
            ->first();

        $newUsers = (int)($users->new_users ?? 0);
        $verifiedNewUsers = (int)($users->verified_new_users ?? 0);

        return [
            'new_users' => $newUsers,
            'verified_new_users' => $verifiedNewUsers,
            'mobile_verification_rate' => $newUsers > 0
                ? round(($verifiedNewUsers / $newUsers) * 100, 2)
                : 0.0,
            'unverified_new_users' => max(0, $newUsers - $verifiedNewUsers),
            'otp_messages' => (int)($otpMessages->otp_messages ?? 0),
            'unique_otp_recipients' => (int)($otpMessages->unique_otp_recipients ?? 0),
            'otp_validation_attempts' => (int)($otpMessages->otp_validation_attempts ?? 0),
            'otp_with_validation_errors' => (int)($otpMessages->otp_with_validation_errors ?? 0),
            'new_comments' => (int)($comments->new_comments ?? 0),
            'approved_comments' => (int)($comments->approved_comments ?? 0),
            'new_tickets' => (int)$tickets,
            'closed_tickets' => (int)$closedTickets,
            'download_sessions' => (int)($downloads->download_sessions ?? 0),
            'unique_downloaders' => (int)($downloads->unique_downloaders ?? 0),
        ];
    }

    /**
     * @param array<string, int|float> $current
     * @param array<string, int|float>|null $previous
     * @return array<string, AnalyticsMetricDTO>
     */
    private function buildMetrics(array $current, ?array $previous): array
    {
        return collect($current)
            ->mapWithKeys(function (int|float $value, string $key) use ($previous): array {
                return [
                    $key => AnalyticsMetricDTO::compare(
                        current: $value,
                        previous: $previous[$key] ?? null,
                    ),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function trend(
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        $buckets = $this->trendBuckets($range, $granularity);

        if ($buckets->isEmpty()) {
            return collect();
        }

        $result = $buckets
            ->map(static fn(array $bucket): array => [
                'date' => $bucket['from']->toISOString(),
                'label' => $bucket['label'],
                'period_label' => $bucket['period_label'],
                'new_users' => 0,
                'otp_messages' => 0,
                'otp_validation_attempts' => 0,
                'comments_count' => 0,
                'tickets_count' => 0,
                'downloads_count' => 0,
            ])
            ->values();

        $lookup = $this->trendBucketLookup($buckets, $granularity);

        $this->applyRows(
            result: $result,
            lookup: $lookup,
            rows: $this->groupedSimpleCount('users', 'created_at', $range, $granularity, 'new_users'),
            columns: ['new_users'],
        );

        $this->applyRows(
            result: $result,
            lookup: $lookup,
            rows: $this->groupedOtpCounts($range, $granularity),
            columns: ['otp_messages', 'otp_validation_attempts'],
        );

        $this->applyRows(
            result: $result,
            lookup: $lookup,
            rows: $this->groupedSimpleCount('comments', 'created_at', $range, $granularity, 'comments_count'),
            columns: ['comments_count'],
        );

        $this->applyRows(
            result: $result,
            lookup: $lookup,
            rows: $this->groupedSimpleCount('tickets', 'created_at', $range, $granularity, 'tickets_count'),
            columns: ['tickets_count'],
        );

        $this->applyRows(
            result: $result,
            lookup: $lookup,
            rows: $this->groupedSimpleCount(
                'purchase_access_downloads',
                'created_at',
                $range,
                $granularity,
                'downloads_count',
            ),
            columns: ['downloads_count'],
        );

        return $result->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $current
     * @param Collection<int, array<string, mixed>>|null $comparison
     * @return Collection<int, array<string, mixed>>
     */
    private function mergeTrendComparison(Collection $current, ?Collection $comparison): Collection
    {
        return $current
            ->values()
            ->map(function (array $row, int $index) use ($comparison): array {
                $comparisonRow = $comparison?->values()->get($index);

                return [
                    ...$row,
                    'comparison_label' => $comparisonRow['period_label'] ?? null,
                    'comparison_new_users' => $comparisonRow['new_users'] ?? null,
                    'comparison_otp_messages' => $comparisonRow['otp_messages'] ?? null,
                    'comparison_otp_validation_attempts' => $comparisonRow['otp_validation_attempts'] ?? null,
                    'comparison_comments_count' => $comparisonRow['comments_count'] ?? null,
                    'comparison_tickets_count' => $comparisonRow['tickets_count'] ?? null,
                    'comparison_downloads_count' => $comparisonRow['downloads_count'] ?? null,
                ];
            });
    }

    /**
     * @return Collection<int, object>
     */
    private function groupedSimpleCount(
        string $table,
        string $dateColumn,
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
        string $alias,
    ): Collection {
        $qualifiedDate = $table . '.' . $dateColumn;
        $bucketExpression = $this->bucketExpression($qualifiedDate, $granularity);

        return DB::table($table)
            ->whereBetween($qualifiedDate, [$range->from, $range->to])
            ->groupByRaw($bucketExpression)
            ->orderByRaw($bucketExpression)
            ->selectRaw($bucketExpression . ' as bucket')
            ->selectRaw('COUNT(*) as ' . $alias)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    private function groupedOtpCounts(
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        $bucketExpression = $this->bucketExpression('otps.created_at', $granularity);

        return DB::table('otps')
            ->where('otps.key_type', 'mobile')
            ->whereBetween('otps.created_at', [$range->from, $range->to])
            ->groupByRaw($bucketExpression)
            ->orderByRaw($bucketExpression)
            ->selectRaw($bucketExpression . ' as bucket')
            ->selectRaw('COUNT(*) as otp_messages')
            ->selectRaw('COALESCE(SUM(attempts), 0) as otp_validation_attempts')
            ->get();
    }

    private function bucketExpression(
        string $qualifiedDate,
        AnalyticsGranularityEnum $granularity,
    ): string {
        return $granularity === AnalyticsGranularityEnum::HOUR
            ? "DATE_FORMAT({$qualifiedDate}, '%Y-%m-%d %H:00:00')"
            : "DATE({$qualifiedDate})";
    }

    /**
     * @param Collection<int, array<string, mixed>> $result
     * @param Collection<string, int> $lookup
     * @param Collection<int, object> $rows
     * @param list<string> $columns
     */
    private function applyRows(
        Collection $result,
        Collection $lookup,
        Collection $rows,
        array $columns,
    ): void {
        foreach ($rows as $row) {
            $index = $lookup->get((string)$row->bucket);

            if ($index === null) {
                continue;
            }

            $current = $result->get($index);

            foreach ($columns as $column) {
                $current[$column] += (int)($row->{$column} ?? 0);
            }

            $result->put($index, $current);
        }
    }

    /**
     * @return Collection<int, array{from: CarbonImmutable, to: CarbonImmutable, label: string, period_label: string}>
     */
    private function trendBuckets(
        AnalyticsDateRangeDTO $range,
        AnalyticsGranularityEnum $granularity,
    ): Collection {
        return match ($granularity) {
            AnalyticsGranularityEnum::HOUR => $this->hourlyTrendBuckets($range),
            AnalyticsGranularityEnum::DAY => $this->dailyTrendBuckets($range),
            AnalyticsGranularityEnum::WEEK => $this->weeklyTrendBuckets($range),
            AnalyticsGranularityEnum::MONTH => $this->monthlyTrendBuckets($range),
            AnalyticsGranularityEnum::AUTO => collect(),
        };
    }

    private function hourlyTrendBuckets(AnalyticsDateRangeDTO $range): Collection
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

    private function dailyTrendBuckets(AnalyticsDateRangeDTO $range): Collection
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

    private function weeklyTrendBuckets(AnalyticsDateRangeDTO $range): Collection
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

    private function monthlyTrendBuckets(AnalyticsDateRangeDTO $range): Collection
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
    private function trendBucketLookup(
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function otpTypePerformance(AnalyticsDateRangeDTO $range): Collection
    {
        return DB::table('otps')
            ->where('key_type', 'mobile')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->groupBy('type')
            ->select('type')
            ->selectRaw('COUNT(*) as messages_count')
            ->selectRaw('COUNT(DISTINCT `key`) as unique_recipients')
            ->selectRaw('COALESCE(SUM(attempts), 0) as validation_attempts')
            ->selectRaw('COALESCE(SUM(CASE WHEN attempts > 0 THEN 1 ELSE 0 END), 0) as messages_with_validation_errors')
            ->orderByDesc('messages_count')
            ->get()
            ->map(function (object $row): array {
                $messages = (int)$row->messages_count;
                $messagesWithErrors = (int)$row->messages_with_validation_errors;
                $type = OtpTypeEnum::tryFrom((string)$row->type);

                return [
                    'type' => (string)$row->type,
                    'label' => $type?->label() ?? $this->fallbackOtpLabel((string)$row->type),
                    'messages_count' => $messages,
                    'unique_recipients' => (int)$row->unique_recipients,
                    'validation_attempts' => (int)$row->validation_attempts,
                    'messages_with_validation_errors' => $messagesWithErrors,
                    'validation_error_rate' => $messages > 0
                        ? round(($messagesWithErrors / $messages) * 100, 2)
                        : 0.0,
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function topDownloadedProducts(AnalyticsDateRangeDTO $range): Collection
    {
        return DB::table('purchase_access_downloads as downloads')
            ->leftJoin('products', 'products.id', '=', 'downloads.product_id')
            ->whereBetween('downloads.created_at', [$range->from, $range->to])
            ->groupBy('downloads.product_id', 'products.title')
            ->select('downloads.product_id', 'products.title')
            ->selectRaw('COUNT(*) as downloads_count')
            ->selectRaw('COUNT(DISTINCT downloads.user_id) as unique_users')
            ->orderByDesc('downloads_count')
            ->limit(7)
            ->get()
            ->map(static fn(object $row): array => [
                'product_id' => $row->product_id !== null ? (int)$row->product_id : null,
                'title' => $row->title ?: 'محصول حذف شده',
                'downloads_count' => (int)$row->downloads_count,
                'unique_users' => (int)$row->unique_users,
            ]);
    }

    /**
     * @return array<string, int>
     */
    private function systemSnapshot(): array
    {
        return [
            'total_users' => DB::table('users')->count(),
            'active_users' => DB::table('users')
                ->whereNull('deleted_at')
                ->where('status', UserStatusEnum::ACTIVE->value)
                ->count(),
            'inactive_users' => DB::table('users')
                ->whereNull('deleted_at')
                ->where('status', UserStatusEnum::INACTIVE->value)
                ->count(),
            'deleted_users' => DB::table('users')
                ->whereNotNull('deleted_at')
                ->count(),
            'pending_comments' => DB::table('comments')
                ->whereNull('deleted_at')
                ->where('status', CommentStatusEnum::PENDING->value)
                ->count(),
            'open_tickets' => DB::table('tickets')
                ->whereNull('deleted_at')
                ->where('status', '!=', TicketStatusEnum::CLOSED->value)
                ->count(),
            'total_page_views' => (int)DB::table('page_views')->sum('count'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function commentStatusBreakdown(AnalyticsDateRangeDTO $range): array
    {
        $rows = DB::table('comments')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->groupBy('status')
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->get()
            ->keyBy('status');

        return [
            'approved' => (int)($rows->get(CommentStatusEnum::APPROVED->value)?->total ?? 0),
            'pending' => (int)($rows->get(CommentStatusEnum::PENDING->value)?->total ?? 0),
            'rejected' => (int)($rows->get(CommentStatusEnum::REJECTED->value)?->total ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function ticketStatusBreakdown(AnalyticsDateRangeDTO $range): array
    {
        $rows = DB::table('tickets')
            ->whereBetween('created_at', [$range->from, $range->to])
            ->groupBy('status')
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->get()
            ->keyBy('status');

        return [
            'open' => (int)($rows->get(TicketStatusEnum::OPEN->value)?->total ?? 0),
            'answered' => (int)($rows->get(TicketStatusEnum::ANSWERED->value)?->total ?? 0),
            'closed' => (int)($rows->get(TicketStatusEnum::CLOSED->value)?->total ?? 0),
        ];
    }

    private function fallbackOtpLabel(string $type): string
    {
        return match (strtolower($type)) {
            'register' => 'ثبت نام',
            'login' => 'ورود',
            'reset_password', 'forgot_password' => 'فراموشی رمز عبور',
            default => $type,
        };
    }
}
