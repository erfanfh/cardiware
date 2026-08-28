<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class GetAudienceClubDashboardCardAction
{
    private const COUPON_LIMIT = 3;

    /** @return array{pointBalance: int, availableCoupons: Collection<int, Coupon>} */
    public function execute(User $user): array
    {
        $now = now();
        $userId = (int) $user->getKey();

        $coupons = RewardRedemption::query()
            ->where('user_id', $userId)
            ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
            ->whereNotNull('coupon_id')
            ->whereHas('coupon', function (Builder $query) use ($now, $userId): void {
                $query
                    ->where('status', CouponStatusEnum::ACTIVE->value)
                    ->where(function (Builder $query) use ($now): void {
                        $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function (Builder $query) use ($now): void {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
                    })
                    ->where(function (Builder $query): void {
                        $query
                            ->whereNull('usage_limit')
                            ->orWhereColumn('used_count', '<', 'usage_limit');
                    })
                    ->where(function (Builder $query) use ($userId): void {
                        $query->whereNull('usage_limit_per_user')
                            ->orWhereRaw(
                                '(select count(*) from coupon_redemptions where coupon_redemptions.coupon_id = coupons.id and coupon_redemptions.user_id = ?) < coupons.usage_limit_per_user',
                                [$userId],
                            );
                    });
            })
            ->with('coupon')
            ->latest('fulfilled_at')
            ->latest('id')
            ->limit(self::COUPON_LIMIT)
            ->get()
            ->pluck('coupon')
            ->filter()
            ->values();

        return [
            'pointBalance' => max(0, (int) $user->getAttribute('loyalty_points')),
            'availableCoupons' => $coupons,
        ];
    }
}
