<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class GetAudienceClubOverviewAction
{
    public function __construct(
        private readonly EvaluateRewardOfferAvailabilityAction $evaluateReward,
    ) {
    }

    /** @return array<string, mixed> */
    public function execute(User $user): array
    {
        $account = $user->pointAccount()->first();
        $balance = (int) ($account?->balance ?? 0);

        $pointEntries = $this->pointEntries($account?->getKey());
        $earningRules = EarningRule::query()
            ->currentlyActive()
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        $rewardOffers = RewardOffer::query()
            ->currentlyActive()
            ->with('target')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function (RewardOffer $offer): bool {
                if ($offer->type !== RewardTypeEnum::RAFFLE_TICKET) {
                    return true;
                }

                $campaign = $offer->target;
                if (!$campaign) {
                    return false;
                }

                return in_array($campaign->status, [
                    DrawCampaignStatusEnum::SCHEDULED,
                    DrawCampaignStatusEnum::OPEN,
                ], true);
            })
            ->values();

        $rewardAvailabilities = $rewardOffers
            ->mapWithKeys(fn(RewardOffer $offer) => [
                $offer->getKey() => $this->evaluateReward->execute($offer, $user),
            ]);

        $rewardRedemptions = RewardRedemption::query()
            ->where('user_id', $user->getKey())
            ->with([
                'rewardOffer.target',
                'coupon',
                'fulfillment' => function (MorphTo $morphTo): void {
                    $morphTo->morphWith([
                        TicketGrant::class => ['campaign', 'tickets'],
                    ]);
                },
                'pointLedgerEntry',
                'reversalPointLedgerEntry',
            ])
            ->latest('id')
            ->paginate(8, ['*'], 'rewards_page')
            ->withQueryString();

        return [
            'pointAccount' => $account,
            'pointBalance' => $balance,
            'spendablePointBalance' => max(0, $balance),
            'lifetimeEarned' => (int) ($account?->lifetime_earned ?? 0),
            'lifetimeSpent' => (int) ($account?->lifetime_spent ?? 0),
            'pointEntries' => $pointEntries,
            'earningRules' => $earningRules,
            'rewardOffers' => $rewardOffers,
            'rewardAvailabilities' => $rewardAvailabilities,
            'rewardRedemptions' => $rewardRedemptions,
            'completedRewardCount' => RewardRedemption::query()
                ->where('user_id', $user->getKey())
                ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
                ->count(),
        ];
    }

    private function pointEntries(?int $accountId): LengthAwarePaginator
    {
        if (!$accountId) {
            return PointLedgerEntry::query()
                ->whereRaw('1 = 0')
                ->paginate(12, ['*'], 'points_page')
                ->withQueryString();
        }

        return PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->with(['earningRule', 'reference', 'reversalOf'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(12, ['*'], 'points_page')
            ->withQueryString();
    }
}
