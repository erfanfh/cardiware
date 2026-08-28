<?php

namespace App\Application\Loyalty\Earning\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class EarningRuleUsageCounter
{
    public function countAwardsOnDate(
        User $user,
        EarningRule $rule,
        CarbonInterface $date,
    ): int {
        $accountId = PointAccount::query()
            ->where('user_id', $user->getKey())
            ->value('id');

        if (!$accountId) {
            return 0;
        }

        $from = $date->copy()->startOfDay();
        $to = $date->copy()->endOfDay();

        return PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where('earning_rule_id', $rule->getKey())
            ->where('type', PointEntryTypeEnum::EARN)
            ->where(function (Builder $query) use ($from, $to): void {
                $query
                    ->whereBetween('occurred_at', [$from, $to])
                    ->orWhere(function (Builder $query) use ($from, $to): void {
                        $query
                            ->whereNull('occurred_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->count();
    }


    public function hasReachedDailyLimit(
        User $user,
        EarningRule $rule,
        int $limit,
        CarbonInterface $date,
    ): bool {
        if ($limit <= 0) {
            return false;
        }

        return $this->countAwardsOnDate($user, $rule, $date) >= $limit;
    }

}
