<?php

namespace App\Application\Marketplace\SellerRewards;

use App\Domain\Marketplace\Models\StoreReward;
use App\Domain\Marketplace\Models\StoreSpotlight;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Support\Str;

/**
 * جایزه‌ی برترین فروشنده:
 *  - سهم اضافه از هر فروش در بازه‌ی جایزه (SnapshotOrderItemCommissionsAction آن را اعمال می‌کند)
 *  - کد تخفیف شخصی برای اعضای فروشگاه (خرید سایر محصولات)
 *  - نمایش محصولات فروشگاه در «فروشگاه ویژه» صفحه اصلی؛ جایگاه بعد از آخرین نمایش
 *    فعال/رزروشده شروع می‌شود تا دو برنده هم‌زمان جای هم را نگیرند.
 */
final class GrantStoreRewardAction
{
    /** @param array<string, int>|null $prize */
    public function execute(StoreReward $reward, ?array $prize): void
    {
        if ($prize === null) {
            return;
        }

        $changes = [];

        if ($prize['commission_bonus_bp'] > 0 && $prize['bonus_days'] > 0) {
            $changes['commission_bonus_bp'] = $prize['commission_bonus_bp'];
            $changes['bonus_starts_at'] = now();
            $changes['bonus_ends_at'] = now()->addDays($prize['bonus_days']);
        }

        $sellerIds = $reward->store->sellers()->pluck('users.id')->all();
        if ($prize['coupon_percent'] > 0 && $sellerIds !== []) {
            $coupon = $this->createCoupon($reward, $prize, $sellerIds);
            $changes['coupon_id'] = $coupon->getKey();
            $changes['coupon_code'] = $coupon->code;
        }

        if ($changes !== []) {
            $reward->forceFill($changes)->save();
        }

        if ($prize['spotlight_days'] > 0) {
            $start = StoreSpotlight::query()->where('ends_at', '>', now())->max('ends_at');
            $start = $start ? \Illuminate\Support\Carbon::parse($start) : now();

            StoreSpotlight::query()->create([
                'store_id' => $reward->store_id,
                'store_reward_id' => $reward->getKey(),
                'source' => 'reward',
                'headline' => $reward->title(),
                'starts_at' => $start,
                'ends_at' => $start->copy()->addDays($prize['spotlight_days']),
            ]);
        }
    }

    /** @param list<int> $sellerIds */
    private function createCoupon(StoreReward $reward, array $prize, array $sellerIds): Coupon
    {
        do {
            $code = 'MWS-' . $reward->store_id . '-' . Str::upper(Str::random(8));
        } while (Coupon::query()->where('code', $code)->exists());

        $coupon = Coupon::query()->create([
            'code' => $code,
            'title' => 'جایزه ' . $reward->title(),
            'description' => 'کد تخفیف جایزه‌ی برترین فروشندگان برای خرید سایر محصولات',
            'type' => CouponTypeEnum::PERCENTAGE,
            'value' => $prize['coupon_percent'],
            'max_discount_amount' => $prize['coupon_max_amount'] > 0 ? $prize['coupon_max_amount'] : null,
            'scope' => CouponScopeEnum::ORDER,
            'status' => CouponStatusEnum::ACTIVE,
            'applies_to_all_users' => false,
            'starts_at' => now(),
            'expires_at' => now()->addDays($prize['coupon_days']),
            'minimum_order_amount' => 0,
            'usage_limit' => 1,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'applies_to_addons' => false,
            'created_by_user_id' => null,
            'metadata' => ['source' => 'seller_reward', 'store_reward_id' => $reward->getKey()],
        ]);

        app(AuditService::class)->sync($coupon, 'users', $sellerIds, false);

        return $coupon;
    }
}
