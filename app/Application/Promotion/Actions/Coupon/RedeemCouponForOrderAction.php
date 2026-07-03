<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Order;
use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponRedemption;
use App\Domain\Promotion\Models\CouponReservation;
use Illuminate\Support\Facades\DB;
use Throwable;

class RedeemCouponForOrderAction
{
    /**
     * @throws Throwable
     */
    public function execute(Order $order): void
    {
        if (!$order->coupon_id || (int)$order->coupon_discount_amount <= 0) {
            return;
        }

        DB::transaction(function () use ($order): void {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$order->coupon_id || (int)$order->coupon_discount_amount <= 0) {
                return;
            }

            $reservation = CouponReservation::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            $alreadyRedeemed = CouponRedemption::query()
                ->where('coupon_id', $order->coupon_id)
                ->where('order_id', $order->id)
                ->exists();

            if ($alreadyRedeemed) {
                if ($reservation && !$reservation->status->isRedeemed()) {
                    $reservation->forceFill([
                        'status' => CouponReservationStatusEnum::REDEEMED,
                        'redeemed_at' => now(),
                    ])->save();
                }

                return;
            }

            /** @var Coupon|null $coupon */
            $coupon = Coupon::query()
                ->whereKey($order->coupon_id)
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                return;
            }

            CouponRedemption::query()->create([
                'coupon_id' => $coupon->id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'code' => $order->coupon_code,
                'order_subtotal' => $order->subtotal_amount,
                'eligible_amount' => $order->coupon_eligible_amount,
                'discount_amount' => $order->coupon_discount_amount,
                'redeemed_at' => now(),
                'metadata' => [
                    'order_number' => $order->order_number,
                    'reservation_id' => $reservation?->id,
                ],
            ]);

            $coupon->forceFill([
                'used_count' => (int)$coupon->used_count + 1,
            ])->save();

            if ($reservation) {
                $reservation->forceFill([
                    'status' => CouponReservationStatusEnum::REDEEMED,
                    'redeemed_at' => now(),
                ])->save();
            }
        });
    }
}
