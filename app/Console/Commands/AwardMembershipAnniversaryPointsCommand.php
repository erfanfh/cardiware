<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\MembershipAnniversaryEarningContext;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class AwardMembershipAnniversaryPointsCommand extends Command
{
    protected $signature = 'loyalty:award-membership-anniversaries
                            {--date= : Target date in Y-m-d format}
                            {--lookback=1 : Number of calendar days to process when --date is omitted}';

    protected $description = 'Award loyalty points for membership anniversaries with an idempotent lookback window.';

    public function handle(ProcessEarningRuleAction $processEarningRuleAction): int
    {
        try {
            $targetDates = $this->resolveTargetDates();
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $totals = [
            'dates' => 0,
            'processed' => 0,
            'awarded' => 0,
            'failed' => 0,
        ];

        foreach ($targetDates as $targetDate) {
            $totals['dates']++;
            $this->processDate($processEarningRuleAction, $targetDate, $totals);
        }

        $this->components->info(sprintf(
            'Membership anniversaries dates=%d, processed=%d, awarded=%d, failed=%d.',
            $totals['dates'],
            $totals['processed'],
            $totals['awarded'],
            $totals['failed'],
        ));

        return $totals['failed'] === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    /** @return list<CarbonImmutable> */
    private function resolveTargetDates(): array
    {
        $timezone = (string) config('app.timezone');
        $date = $this->option('date');

        if (filled($date)) {
            return [
                CarbonImmutable::createFromFormat('!Y-m-d', (string) $date, $timezone)
                    ->startOfDay(),
            ];
        }

        $lookback = max(1, min(30, (int) $this->option('lookback')));
        $today = now($timezone)->startOfDay()->toImmutable();
        $dates = [];

        for ($daysAgo = $lookback - 1; $daysAgo >= 0; $daysAgo--) {
            $dates[] = $today->subDays($daysAgo);
        }

        return $dates;
    }

    private function processDate(
        ProcessEarningRuleAction $processEarningRuleAction,
        CarbonImmutable $targetDate,
        array &$totals,
    ): void {
        $query = User::query()
            ->where('status', UserStatusEnum::ACTIVE->value)
            ->whereYear('created_at', '<', $targetDate->year)
            ->whereMonth('created_at', $targetDate->month);

        if ($this->shouldIncludeLeapDayMembers($targetDate)) {
            $query->where(function ($query): void {
                $query
                    ->whereDay('created_at', 28)
                    ->orWhereDay('created_at', 29);
            });
        } else {
            $query->whereDay('created_at', $targetDate->day);
        }

        $query->chunkById(500, function ($users) use (
            $processEarningRuleAction,
            $targetDate,
            &$totals,
        ): void {
            foreach ($users as $user) {
                $totals['processed']++;

                try {
                    $membershipYears = $targetDate->year - $user->created_at->year;

                    $result = $processEarningRuleAction->execute(
                        EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY,
                        new MembershipAnniversaryEarningContext(
                            user: $user,
                            membershipYears: $membershipYears,
                            occurredAt: $targetDate,
                        ),
                    );

                    if ($result->wasAwarded()) {
                        $totals['awarded']++;
                    }
                } catch (Throwable $exception) {
                    $totals['failed']++;
                    report($exception);
                }
            }
        });
    }

    private function shouldIncludeLeapDayMembers(CarbonImmutable $targetDate): bool
    {
        return $targetDate->month === 2
            && $targetDate->day === 28
            && !$targetDate->isLeapYear();
    }
}
