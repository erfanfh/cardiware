<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Order;
use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponReservation;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReserveCouponForOrderAction
{
    private const RESERVATION_TTL_MINUTES = 40;

    /**
     * @throws Throwable
     */
    public function execute(Order $order): ?CouponReservation
    {
        if (!$order->coupon_id || (int)$order->coupon_discount_amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order): CouponReservation {
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$order->coupon_id || (int)$order->coupon_discount_amount <= 0) {
                throw new DomainException('Order has no coupon to reserve.');
            }

            /** @var Coupon $coupon */
            $coupon = Coupon::query()
                ->whereKey($order->coupon_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->expireExpiredReservations($coupon);

            $existingReservation = CouponReservation::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($existingReservation instanceof CouponReservation) {
                if ($this->isActiveAndNotExpired($existingReservation)) {
                    return $existingReservation;
                }

                if ($existingReservation->status->isRedeemed()) {
                    return $existingReservation;
                }
            }

            if (!$coupon->isCurrentlyUsable()) {
                throw new DomainException('Coupon is not currently usable.');
            }

            $this->ensureGlobalCapacityIsAvailable($coupon);

            $this->ensureUserCapacityIsAvailable($coupon, $order);

            $reservationData = [
                'coupon_id' => $coupon->id,
                'user_id' => $order->user_id,
                'cart_id' => $order->cart_id,
                'order_id' => $order->id,

                /*
                 * Important:
                 * Keep the coupon code snapshot from the order.
                 * If admin changes coupon code later, this order still keeps
                 * the code that was applied when the order was created.
                 */
                'code' => $order->coupon_code ?: $coupon->code,

                'eligible_amount' => (int)$order->coupon_eligible_amount,
                'discount_amount' => (int)$order->coupon_discount_amount,

                'status' => CouponReservationStatusEnum::ACTIVE,
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes(self::RESERVATION_TTL_MINUTES),
                'redeemed_at' => null,
                'released_at' => null,
                'expired_at' => null,

                'metadata' => $this->reservationMetadata($order, $existingReservation),
            ];

            if ($existingReservation instanceof CouponReservation) {
                $existingReservation->forceFill($reservationData)->save();

                return $existingReservation->refresh();
            }

            return CouponReservation::query()->create($reservationData);
        });
    }

    private function expireExpiredReservations(Coupon $coupon): void
    {
        CouponReservation::query()
            ->where('coupon_id', $coupon->id)
            ->where('status', CouponReservationStatusEnum::ACTIVE->value)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => CouponReservationStatusEnum::EXPIRED->value,
                'expired_at' => now(),
            ]);
    }

    private function ensureGlobalCapacityIsAvailable(Coupon $coupon): void
    {
        if ($coupon->usage_limit === null) {
            return;
        }

        $activeReservationsCount = CouponReservation::query()
            ->where('coupon_id', $coupon->id)
            ->where('status', CouponReservationStatusEnum::ACTIVE->value)
            ->where('expires_at', '>', now())
            ->count();

        $reservedAndUsedCount = (int)$coupon->used_count + $activeReservationsCount;

        if ($reservedAndUsedCount >= (int)$coupon->usage_limit) {
            throw new DomainException('Coupon reservation limit reached.');
        }
    }

    private function ensureUserCapacityIsAvailable(Coupon $coupon, Order $order): void
    {
        if ($coupon->usage_limit_per_user === null || !$order->user_id) {
            return;
        }

        $redeemedByUserCount = $coupon->redemptions()
            ->where('user_id', $order->user_id)
            ->count();

        $activeReservationsByUserCount = CouponReservation::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $order->user_id)
            ->where('status', CouponReservationStatusEnum::ACTIVE->value)
            ->where('expires_at', '>', now())
            ->count();

        $reservedAndRedeemedByUserCount = $redeemedByUserCount + $activeReservationsByUserCount;

        if ($reservedAndRedeemedByUserCount >= (int)$coupon->usage_limit_per_user) {
            throw new DomainException('Coupon per user reservation limit reached.');
        }
    }

    private function isActiveAndNotExpired(CouponReservation $reservation): bool
    {
        return $reservation->status->isActive()
            && $reservation->expires_at !== null
            && $reservation->expires_at->isFuture();
    }

    /**
     * @return array<string, mixed>
     */
    private function reservationMetadata(Order $order, ?CouponReservation $existingReservation): array
    {
        $metadata = $existingReservation?->metadata ?? [];

        $metadata['order_number'] = $order->order_number;
        $metadata['source'] = $existingReservation ? 'retry' : 'checkout';
        $metadata['reserved_at'] = now()->toDateTimeString();

        if ($existingReservation) {
            $metadata['previous_status'] = $existingReservation->status->value;
            $metadata['reactivated_at'] = now()->toDateTimeString();
        }

        return $metadata;
    }
}
