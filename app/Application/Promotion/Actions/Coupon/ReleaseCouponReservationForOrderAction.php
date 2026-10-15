<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Order;
use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Models\CouponReservation;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReleaseCouponReservationForOrderAction
{
    /**
     * @throws Throwable
     */
    public function execute(Order $order, string $reason = 'released'): void
    {
        DB::transaction(function () use ($order, $reason): void {
            $reservations = CouponReservation::query()
                ->where('order_id', $order->id)
                ->where('status', CouponReservationStatusEnum::ACTIVE)
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                $metadata = $reservation->metadata ?? [];
                $metadata['release_reason'] = $reason;

                $reservation->forceFill([
                    'status' => CouponReservationStatusEnum::RELEASED,
                    'released_at' => now(),
                    'metadata' => $metadata,
                ])->save();
            }
        });
    }
}
