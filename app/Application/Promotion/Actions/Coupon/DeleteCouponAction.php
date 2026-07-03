<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponReservation;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteCouponAction
{
    /**
     * @throws Throwable
     */
    public function execute(Coupon $coupon): void
    {
        DB::transaction(function () use ($coupon): void {
            $coupon = Coupon::query()
                ->whereKey($coupon->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->releaseActiveReservations($coupon);

            $coupon->delete();
        });
    }

    private function releaseActiveReservations(Coupon $coupon): void
    {
        $reservations = CouponReservation::query()
            ->where('coupon_id', $coupon->id)
            ->where('status', CouponReservationStatusEnum::ACTIVE)
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->get();

        foreach ($reservations as $reservation) {
            $metadata = $reservation->metadata ?? [];

            $metadata['release_reason'] = 'coupon_deleted';
            $metadata['released_by'] = self::class;
            $metadata['released_at'] = now()->toDateTimeString();

            $reservation->forceFill([
                'status' => CouponReservationStatusEnum::RELEASED,
                'released_at' => now(),
                'metadata' => $metadata,
            ])->save();
        }
    }
}
