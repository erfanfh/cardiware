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
                            {--date= : Target date in Y-m-d format}';

    protected $description = 'Award loyalty points for membership anniversaries.';

    public function handle(ProcessEarningRuleAction $processEarningRuleAction): int
    {
        try {
            $targetDate = $this->resolveTargetDate();
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $processed = 0;
        $awarded = 0;
        $failed = 0;

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
            &$processed,
            &$awarded,
            &$failed,
        ): void {
            foreach ($users as $user) {
                $processed++;

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
                        $awarded++;
                    }
                } catch (Throwable $exception) {
                    $failed++;
                    report($exception);
                }
            }
        });

        $this->components->info(
            "Membership anniversaries processed: {$processed}, awarded: {$awarded}, failed: {$failed}."
        );

        return $failed === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function resolveTargetDate(): CarbonImmutable
    {
        $timezone = (string)config('app.timezone');
        $date = $this->option('date');

        if (blank($date)) {
            return now($timezone)->startOfDay()->toImmutable();
        }

        return CarbonImmutable::createFromFormat('!Y-m-d', (string)$date, $timezone)
            ->startOfDay();
    }

    private function shouldIncludeLeapDayMembers(CarbonImmutable $targetDate): bool
    {
        return $targetDate->month === 2
            && $targetDate->day === 28
            && !$targetDate->isLeapYear();
    }
}
