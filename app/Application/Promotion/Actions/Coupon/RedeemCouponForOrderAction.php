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
        DB::transaction(function () use ($order): void {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $snapshots = collect($order->appliedCouponSnapshots())
                ->filter(fn (array $snapshot): bool => ! empty($snapshot['coupon_id']) && (int) $snapshot['discount_amount'] > 0)
                ->sortBy('coupon_id');
            $coupons = Coupon::withTrashed()->whereIn('id', $snapshots->pluck('coupon_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($snapshots as $snapshot) {
                $coupon = $coupons->get($snapshot['coupon_id']);
                if ($coupon) {
                    $this->redeem($order, $coupon, $snapshot);
                }
            }
        });
    }

    private function redeem(Order $order, Coupon $coupon, array $snapshot): void
    {
        $reservation = CouponReservation::query()
            ->where('order_id', $order->id)
            ->where('coupon_id', $coupon->id)
            ->lockForUpdate()
            ->first();

        $alreadyRedeemed = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('order_id', $order->id)
            ->exists();

        if ($alreadyRedeemed) {
            if ($reservation && ! $reservation->status->isRedeemed()) {
                $reservation->forceFill([
                    'status' => CouponReservationStatusEnum::REDEEMED,
                    'redeemed_at' => now(),
                ])->save();
            }

            return;
        }

        CouponRedemption::query()->create([
            'coupon_id' => $coupon->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'code' => $snapshot['code'],
            'order_subtotal' => $order->subtotal_amount,
            'eligible_amount' => (int) $snapshot['eligible_amount'],
            'discount_amount' => (int) $snapshot['discount_amount'],
            'redeemed_at' => now(),
            'metadata' => [
                'order_number' => $order->order_number,
                'reservation_id' => $reservation?->id,
            ],
        ]);

        $coupon->forceFill([
            'used_count' => (int) $coupon->used_count + 1,
        ])->save();

        if ($reservation) {
            $reservation->forceFill([
                'status' => CouponReservationStatusEnum::REDEEMED,
                'redeemed_at' => now(),
            ])->save();
        }
    }
}
