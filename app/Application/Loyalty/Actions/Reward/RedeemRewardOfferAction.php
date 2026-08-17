<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Actions\SpendPointsAction;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Application\Loyalty\Exceptions\RewardOfferUnavailableException;
use App\Application\Loyalty\Exceptions\RewardRedemptionIdempotencyConflictException;
use App\Application\Loyalty\Exceptions\RewardRedemptionLimitExceededException;
use App\Application\Promotion\Actions\Coupon\CreateCouponAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RedeemRewardOfferAction
{
    public function __construct(
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccountAction,
        private readonly SpendPointsAction $spendPointsAction,
        private readonly CreateCouponAction $createCouponAction,
    ) {
    }

    /** @throws Throwable */
    public function execute(RewardOffer $rewardOffer, User $user, string $idempotencyKey): RewardRedemption
    {
        if ($existing = $this->findExisting($rewardOffer, $user, $idempotencyKey)) {
            return $existing;
        }

        return DB::transaction(function () use ($rewardOffer, $user, $idempotencyKey): RewardRedemption {
            $lockedOffer = RewardOffer::query()
                ->whereKey($rewardOffer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($existing = $this->findExisting($lockedOffer, $user, $idempotencyKey)) {
                return $existing;
            }

            $this->assertAvailable($lockedOffer, $user);

            $account = $this->getOrCreatePointAccountAction->execute($user);

            $ledgerEntry = $this->spendPointsAction->execute(new SpendPointsDTO(
                account: $account,
                amount: (int)$lockedOffer->points_cost,
                reason: 'reward_redemption',
                idempotencyKey: 'loyalty:reward:' . $lockedOffer->id . ':' . $idempotencyKey,
                actor: $user,
                reference: $lockedOffer,
                description: 'دریافت پاداش «' . $lockedOffer->title . '» از باشگاه مخاطبان',
                metadata: [
                    'reward_offer_id' => $lockedOffer->id,
                    'reward_offer_code' => $lockedOffer->code,
                ],
            ));

            $coupon = match ($lockedOffer->type) {
                RewardTypeEnum::COUPON => $this->createCoupon($lockedOffer, $user),
            };

            $redemption = RewardRedemption::query()->create([
                'reward_offer_id' => $lockedOffer->id,
                'user_id' => $user->id,
                'point_ledger_entry_id' => $ledgerEntry->id,
                'coupon_id' => $coupon?->id,
                'points_spent' => (int)$lockedOffer->points_cost,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'reward_title' => $lockedOffer->title,
                    'reward_type' => $lockedOffer->type->value,
                    'reward_config' => $lockedOffer->config,
                ],
            ]);

            $lockedOffer->forceFill([
                'redeemed_count' => (int)$lockedOffer->redeemed_count + 1,
            ])->save();

            return $redemption->load(['rewardOffer', 'coupon']);
        }, 3);
    }

    private function assertAvailable(RewardOffer $rewardOffer, User $user): void
    {
        if (!$rewardOffer->isCurrentlyActive()) {
            throw new RewardOfferUnavailableException('این پاداش در حال حاضر قابل دریافت نیست.');
        }

        if ($rewardOffer->hasReachedGlobalLimit()) {
            throw new RewardOfferUnavailableException('ظرفیت دریافت این پاداش تکمیل شده است.');
        }

        if ($rewardOffer->redemption_limit_per_user !== null) {
            $userRedemptions = RewardRedemption::query()
                ->where('reward_offer_id', $rewardOffer->id)
                ->where('user_id', $user->id)
                ->count();

            if ($userRedemptions >= (int)$rewardOffer->redemption_limit_per_user) {
                throw new RewardRedemptionLimitExceededException('سقف دریافت این پاداش برای حساب شما تکمیل شده است.');
            }
        }
    }

    private function findExisting(RewardOffer $rewardOffer, User $user, string $idempotencyKey): ?RewardRedemption
    {
        $existing = RewardRedemption::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$existing) {
            return null;
        }

        if ((int)$existing->reward_offer_id !== (int)$rewardOffer->id || (int)$existing->user_id !== (int)$user->id) {
            throw new RewardRedemptionIdempotencyConflictException('شناسه درخواست دریافت پاداش با درخواست دیگری استفاده شده است.');
        }

        return $existing->loadMissing(['rewardOffer', 'coupon']);
    }

    /** @throws Throwable */
    private function createCoupon(RewardOffer $rewardOffer, User $user): Coupon
    {
        $config = $rewardOffer->config;
        $expiresAfterDays = isset($config['expires_after_days']) && $config['expires_after_days'] !== null
            ? (int)$config['expires_after_days']
            : null;

        return $this->createCouponAction->execute([
            'status' => CouponStatusEnum::ACTIVE->value,
            'title' => 'پاداش باشگاه - ' . $rewardOffer->title,
            'code' => $this->generateCouponCode($user),
            'description' => 'کد تخفیف اختصاصی دریافت شده از باشگاه مخاطبان مودینگ وی.',
            'type' => $config['coupon_type'],
            'value' => (int)$config['coupon_value'],
            'max_discount_amount' => $config['max_discount_amount'] ?? null,
            'minimum_order_amount' => (int)($config['minimum_order_amount'] ?? 0),
            'starts_at' => now(),
            'expires_at' => $expiresAfterDays ? now()->addDays($expiresAfterDays) : null,
            'usage_limit' => 1,
            'usage_limit_per_user' => 1,
            'scope' => CouponScopeEnum::ORDER->value,
            'applies_to_all_users' => false,
            'user_ids' => [$user->id],
            'product_ids' => [],
            'store_id' => null,
            'applies_to_addons' => (bool)($config['applies_to_addons'] ?? false),
        ], $user);
    }

    private function generateCouponCode(User $user): string
    {
        do {
            $code = 'MWR-' . $user->id . '-' . Str::upper(Str::random(10));
        } while (Coupon::withTrashed()->where('code', $code)->exists());

        return $code;
    }
}
