<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Models\CouponReservation;

class ExpireCouponReservationsAction
{
    public function execute(): int
    {
        return CouponReservation::query()
            ->where('status', CouponReservationStatusEnum::ACTIVE)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => CouponReservationStatusEnum::EXPIRED,
                'expired_at' => now(),
            ]);
    }
}
