<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Models\Coupon;

class EnsureSingleFloatingCouponAction
{
    public function execute(Coupon $coupon): void
    {
        if (! $coupon->is_floating_box_enabled) {
            return;
        }

        Coupon::query()
            ->whereKeyNot($coupon->getKey())
            ->where('is_floating_box_enabled', true)
            ->get()->each(fn (Coupon $other) => $other->update(['is_floating_box_enabled' => false]));
    }
}
