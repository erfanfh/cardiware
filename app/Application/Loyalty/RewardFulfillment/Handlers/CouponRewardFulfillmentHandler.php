<?php

namespace App\Application\Loyalty\RewardFulfillment\Handlers;

use App\Application\Loyalty\RewardFulfillment\Contracts\RewardFulfillmentHandler;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class CouponRewardFulfillmentHandler implements RewardFulfillmentHandler
{
    public function type(): RewardTypeEnum
    {
        return RewardTypeEnum::COUPON;
    }

    public function fulfill(
        RewardOffer $offer,
        RewardRedemption $redemption,
        User $user,
    ): Model {
        $config = $offer->config ?? [];
        $isRegistrationGift = data_get($redemption->metadata, 'source') === 'registration_gift';
        $couponType = CouponTypeEnum::from((string) $config['coupon_type']);
        $expiresAfterDays = filled($config['expires_after_days'] ?? null)
            ? (int) $config['expires_after_days']
            : null;

        $coupon = Coupon::query()->create([
            'code' => $this->uniqueCode($user),
            'title' => $offer->title,
            'description' => ($isRegistrationGift
                ? 'هدیه ثبت‌نام باشگاه مخاطبان: '
                : 'پاداش باشگاه مخاطبان: ').$offer->title,
            'type' => $couponType,
            'value' => (int) $config['coupon_value'],
            'max_discount_amount' => $couponType->isPercentage()
                ? ($config['max_discount_amount'] ?? null)
                : null,
            'scope' => CouponScopeEnum::ORDER,
            'status' => CouponStatusEnum::ACTIVE,
            'applies_to_all_users' => false,
            'starts_at' => now(),
            'expires_at' => $expiresAfterDays ? now()->addDays($expiresAfterDays) : null,
            'minimum_order_amount' => (int) ($config['minimum_order_amount'] ?? 0),
            'usage_limit' => 1,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'applies_to_addons' => (bool) ($config['applies_to_addons'] ?? false),
            'created_by_user_id' => null,
            'metadata' => [
                'source' => $isRegistrationGift ? 'loyalty_registration_gift' : 'loyalty_reward',
                'reward_offer_id' => $offer->getKey(),
                'reward_redemption_id' => $redemption->getKey(),
            ],
        ]);

        app(AuditService::class)->sync($coupon, 'users', [$user->getKey()], false);

        return $coupon;
    }

    private function uniqueCode(User $user): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = 'MWR-'.$user->getKey().'-'.Str::upper(Str::random(10));

            if (! Coupon::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique loyalty reward coupon code.');
    }
}
