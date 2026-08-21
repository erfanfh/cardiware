<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Application\Loyalty\Exceptions\RewardOfferUnavailableException;
use App\Application\Loyalty\Exceptions\RewardRedemptionIdempotencyConflictException;
use App\Application\Loyalty\Exceptions\RewardRedemptionLimitExceededException;
use App\Application\Loyalty\RewardFulfillment\Support\RewardFulfillmentHandlerResolver;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

final class GrantRegistrationRewardAction
{
    public function __construct(
        private readonly LoyaltySettings $settings,
        private readonly RewardFulfillmentHandlerResolver $handlerResolver,
    ) {}

    /** @throws Throwable */
    public function execute(User $user, CarbonImmutable $registeredAt): ?RewardRedemption
    {
        if (! $this->settings->get('registration_gift_enabled', false)) {
            return null;
        }

        $offerId = (int) $this->settings->get('registration_gift_reward_offer_id', 0);

        if ($offerId <= 0) {
            return null;
        }

        $idempotencyKey = 'loyalty:registration-gift:user:'.$user->getKey();

        if ($existing = $this->findExisting($idempotencyKey, $user)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($offerId, $user, $registeredAt, $idempotencyKey): RewardRedemption {
                $offer = RewardOffer::query()
                    ->whereKey($offerId)
                    ->lockForUpdate()
                    ->first();

                if (! $offer || ! $offer->isCurrentlyActive()) {
                    throw new RewardOfferUnavailableException(
                        'The configured registration gift is not currently active.'
                    );
                }

                if ($existing = $this->findExisting($idempotencyKey, $user)) {
                    return $existing;
                }

                if ($offer->hasReachedGlobalLimit()) {
                    throw new RewardRedemptionLimitExceededException(
                        'The registration gift global redemption limit has been reached.'
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
                        'The registration gift per-user redemption limit has been reached.'
                    );
                }

                $offer->loadMissing('target');

                $redemption = RewardRedemption::query()->create([
                    'reward_offer_id' => $offer->getKey(),
                    'user_id' => $user->getKey(),
                    'point_ledger_entry_id' => null,
                    'points_spent' => 0,
                    'status' => RewardRedemptionStatusEnum::PENDING,
                    'idempotency_key' => $idempotencyKey,
                    'metadata' => [
                        'source' => 'registration_gift',
                        'registered_at' => $registeredAt->toIso8601String(),
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
                ]);
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->findExisting($idempotencyKey, $user)) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function findExisting(string $idempotencyKey, User $user): ?RewardRedemption
    {
        $redemption = RewardRedemption::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (! $redemption) {
            return null;
        }

        if ((int) $redemption->user_id !== (int) $user->getKey()) {
            throw new RewardRedemptionIdempotencyConflictException(
                'The registration gift idempotency key belongs to another user.'
            );
        }

        return $redemption;
    }
}
