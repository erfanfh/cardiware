<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetAudienceClubOverviewAction
{
    /**
     * @return array{
     *     account: PointAccount|null,
     *     balance: int,
     *     spendable_balance: int,
     *     debt: int,
     *     lifetime_earned: int,
     *     lifetime_spent: int,
     *     entries_count: int,
     *     earning_rules: Collection<int, EarningRule>,
     *     reward_offers: Collection<int, RewardOffer>,
     *     reward_redemptions: LengthAwarePaginator<int, RewardRedemption>,
     *     entries: LengthAwarePaginator<int, PointLedgerEntry>
     * }
     */
    public function execute(User $user, int $perPage = 10): array
    {
        $account = $user->pointAccount()->first();

        $rules = EarningRule::query()
            ->currentlyActive()
            ->orderBy('type')
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get()
            ->unique(fn(EarningRule $rule): string => $rule->type->value)
            ->values();

        $rewardOffers = RewardOffer::query()
            ->currentlyActive()
            ->withCount([
                'redemptions as user_redemptions_count' => fn($query) => $query->where('user_id', $user->id),
            ])
            ->orderBy('sort_order')
            ->orderBy('points_cost')
            ->orderBy('id')
            ->get();

        $rewardRedemptions = RewardRedemption::query()
            ->with([
                'rewardOffer:id,title,type,deleted_at',
                'coupon:id,code,type,value,max_discount_amount,minimum_order_amount,status,starts_at,expires_at,usage_limit,used_count,deleted_at',
            ])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(6, ['*'], 'reward_page')
            ->withQueryString()
            ->fragment('rewards');

        $entriesQuery = PointLedgerEntry::query()
            ->with(['earningRule:id,title,type'])
            ->when(
                $account,
                fn($query) => $query->where('point_account_id', $account->id),
                fn($query) => $query->whereRaw('1 = 0'),
            )
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        $entries = $entriesQuery
            ->paginate(max(5, min($perPage, 30)))
            ->withQueryString()
            ->fragment('point-history');

        $balance = (int)($account?->balance ?? 0);

        return [
            'account' => $account,
            'balance' => $balance,
            'spendable_balance' => max(0, $balance),
            'debt' => max(0, -$balance),
            'lifetime_earned' => (int)($account?->lifetime_earned ?? 0),
            'lifetime_spent' => (int)($account?->lifetime_spent ?? 0),
            'entries_count' => $account ? (int)$account->entries()->count() : 0,
            'earning_rules' => $rules,
            'reward_offers' => $rewardOffers,
            'reward_redemptions' => $rewardRedemptions,
            'entries' => $entries,
        ];
    }
}
