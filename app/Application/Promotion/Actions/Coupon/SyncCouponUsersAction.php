<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Models\Coupon;

class SyncCouponUsersAction
{
    public function execute(Coupon $coupon, array $userIds): void
    {
        if ($coupon->applies_to_all_users) {
            $coupon->users()->sync([]);

            return;
        }

        $userIds = collect($userIds)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $coupon->users()->sync($userIds);
    }
}
