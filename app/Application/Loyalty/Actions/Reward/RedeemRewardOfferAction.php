<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Actions\SpendPointsAction;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Application\Loyalty\Exceptions\RewardOfferUnavailableException;
use App\Application\Loyalty\Exceptions\RewardRedemptionLimitExceededException;
use App\Application\Loyalty\Exceptions\RewardRedemptionIdempotencyConflictException;
use App\Application\Loyalty\RewardFulfillment\Support\RewardFulfillmentHandlerResolver;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class RedeemRewardOfferAction
{
    public function __construct(
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccount,
        private readonly SpendPointsAction $spendPoints,
        private readonly RewardFulfillmentHandlerResolver $handlerResolver,
    ) {
    }

    /** @throws Throwable */
    public function execute(
        RewardOffer $rewardOffer,
        User $user,
        string $idempotencyKey,
    ): RewardRedemption {
        $this->validate($rewardOffer, $user, $idempotencyKey);

        if ($existing = $this->findExisting($idempotencyKey, $rewardOffer, $user)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($rewardOffer, $user, $idempotencyKey): RewardRedemption {
                $offer = RewardOffer::query()
                    ->whereKey($rewardOffer->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($existing = $this->findExisting($idempotencyKey, $offer, $user)) {
                    return $existing;
                }

                if (!$offer->isCurrentlyActive()) {
                    throw new RewardOfferUnavailableException(
                        'This reward offer is not currently active.'
                    );
                }

                if ($offer->hasReachedGlobalLimit()) {
                    throw new RewardRedemptionLimitExceededException(
                        'The global redemption limit for this reward has been reached.'
                    );
                }

                $completedUserRedemptions = RewardRedemption::query()
                    ->where('reward_offer_id', $offer->getKey())
                    ->where('user_id', $user->getKey())
                    ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
                    ->count();

                if (
                    $offer->redemption_limit_per_user !== null
                    && $completedUserRedemptions >= (int) $offer->redemption_limit_per_user
                ) {
                    throw new RewardRedemptionLimitExceededException(
                        'The per-user redemption limit for this reward has been reached.'
                    );
                }

                $offer->loadMissing('target');
                $account = $this->getOrCreatePointAccount->execute($user);

                $spendEntry = $this->spendPoints->execute(new SpendPointsDTO(
                    account: $account,
                    amount: (int) $offer->points_cost,
                    reason: 'reward_redemption',
                    idempotencyKey: 'loyalty:reward:' . $idempotencyKey . ':spend',
                    actor: $user,
                    reference: $offer,
                    description: 'دریافت پاداش: ' . $offer->title,
                    metadata: [
                        'reward_offer_id' => $offer->getKey(),
                        'reward_type' => $offer->type->value,
                    ],
                ));

                $redemption = RewardRedemption::query()->create([
                    'reward_offer_id' => $offer->getKey(),
                    'user_id' => $user->getKey(),
                    'point_ledger_entry_id' => $spendEntry->getKey(),
                    'points_spent' => (int) $offer->points_cost,
                    'status' => RewardRedemptionStatusEnum::PENDING,
                    'idempotency_key' => $idempotencyKey,
                    'metadata' => [
                        'reward_snapshot' => [
                            'code' => $offer->code,
                            'title' => $offer->title,
                            'type' => $offer->type->value,
                            'points_cost' => (int) $offer->points_cost,
                            'config' => $offer->config,
                            'target_type' => $offer->target_type,
                            'target_id' => $offer->target_id,
                        ],
                    ],
                ]);

                $fulfillment = $this->handlerResolver
                    ->resolve($offer->type)
                    ->fulfill($offer, $redemption, $user);

                $redemption->forceFill([
                    'coupon_id' => $fulfillment instanceof Coupon
                        ? $fulfillment->getKey()
                        : null,
                    'fulfillment_type' => $fulfillment->getMorphClass(),
                    'fulfillment_id' => $fulfillment->getKey(),
                    'status' => RewardRedemptionStatusEnum::COMPLETED,
                    'fulfilled_at' => now(),
                ])->save();

                $offer->forceFill([
                    'redeemed_count' => (int) $offer->redeemed_count + 1,
                ])->save();

                return $redemption->loadMissing([
                    'rewardOffer.target',
                    'coupon',
                    'fulfillment',
                    'pointLedgerEntry',
                ]);
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->findExisting($idempotencyKey, $rewardOffer, $user)) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function validate(RewardOffer $rewardOffer, User $user, string $idempotencyKey): void
    {
        if (!$rewardOffer->exists) {
            throw new InvalidArgumentException('Reward offer must exist before it can be redeemed.');
        }

        if (!$user->exists) {
            throw new InvalidArgumentException('Reward redemption user must exist.');
        }

        if (trim($idempotencyKey) === '' || Str::length($idempotencyKey) > 120) {
            throw new InvalidArgumentException(
                'Reward redemption idempotency key is required and cannot exceed 120 characters.'
            );
        }
    }

    private function findExisting(
        string $idempotencyKey,
        RewardOffer $rewardOffer,
        User $user,
    ): ?RewardRedemption {
        $redemption = RewardRedemption::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$redemption) {
            return null;
        }

        if (
            (int) $redemption->reward_offer_id !== (int) $rewardOffer->getKey()
            || (int) $redemption->user_id !== (int) $user->getKey()
        ) {
            throw new RewardRedemptionIdempotencyConflictException(
                'The reward redemption idempotency key has already been used for another operation.'
            );
        }

        return $redemption;
    }
}
