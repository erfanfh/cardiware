<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Application\Loyalty\Actions\ReversePointEntryAction;
use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class RefundRaffleTicketRewardRedemptionAction
{
    public function __construct(
        private readonly ReversePointEntryAction $reversePointEntry,
    ) {
    }

    /** @throws Throwable */
    public function execute(
        RewardRedemption $redemption,
        string $reason,
        ?User $actor = null,
    ): RewardRedemption {
        if (!$redemption->exists) {
            throw new InvalidArgumentException('Reward redemption must exist before it can be refunded.');
        }

        return DB::transaction(function () use ($redemption, $reason, $actor): RewardRedemption {
            $offer = RewardOffer::query()
                ->withTrashed()
                ->whereKey($redemption->reward_offer_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = RewardRedemption::query()
                ->whereKey($redemption->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === RewardRedemptionStatusEnum::REVERSED) {
                return $locked;
            }

            if ($offer->type !== RewardTypeEnum::RAFFLE_TICKET) {
                throw new InvalidArgumentException(
                    'Only raffle ticket reward redemptions can be refunded by this action.'
                );
            }

            if ($locked->status !== RewardRedemptionStatusEnum::COMPLETED) {
                return $locked;
            }

            $pointEntry = $locked->pointLedgerEntry()->firstOrFail();

            $reversal = $this->reversePointEntry->execute(new ReversePointEntryDTO(
                entry: $pointEntry,
                amount: (int) $locked->points_spent,
                idempotencyKey: 'loyalty:reward-redemption:' . $locked->getKey() . ':raffle-refund',
                actor: $actor,
                reference: $locked,
                description: $reason,
                metadata: [
                    'reward_redemption_id' => $locked->getKey(),
                    'reward_offer_id' => $offer->getKey(),
                    'refund_source' => 'raffle',
                ],
            ));

            $locked->forceFill([
                'status' => RewardRedemptionStatusEnum::REVERSED,
                'reversal_point_ledger_entry_id' => $reversal->getKey(),
                'reversed_at' => now(),
                'metadata' => array_merge($locked->metadata ?? [], [
                    'refund' => [
                        'reason' => $reason,
                        'at' => now()->toISOString(),
                        'actor_user_id' => $actor?->getKey(),
                    ],
                ]),
            ])->save();

            $offer->forceFill([
                'redeemed_count' => max(0, (int) $offer->redeemed_count - 1),
            ])->save();

            return $locked->refresh();
        }, 3);
    }
}
