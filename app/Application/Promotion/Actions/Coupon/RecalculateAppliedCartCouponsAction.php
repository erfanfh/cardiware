<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecalculateAppliedCartCouponsAction
{
    public function __construct(
        private readonly CalculateCouponDiscountForCartAction $calculateCouponDiscountForCartAction,
        private readonly ApplyCouponDiscountToCartItemsAction $applyCouponDiscountToCartItemsAction,
    ) {}

    public function execute(Cart $cart, ?int $requiredCouponId = null, ?User $user = null): array
    {
        $snapshots = collect($cart->appliedCouponSnapshots());
        if ($snapshots->isEmpty()) {
            $this->applyCouponDiscountToCartItemsAction->execute($cart);

            return [];
        }
        $ids = $snapshots->pluck('coupon_id')->filter()->all();
        $codes = $snapshots->whereNull('coupon_id')->pluck('code')->filter()
            ->map(fn (string $code): string => mb_strtolower($code))->all();

        $coupons = Coupon::query()->with(['targets', 'couponUsers'])
            ->where(function ($query) use ($ids, $codes) {
                $query->whereIn('id', $ids);
                if ($codes !== []) {
                    $query->orWhereIn(DB::raw('LOWER(code)'), $codes);
                }
            })->orderBy('id')->lockForUpdate()->get();

        $entries = $snapshots->map(function (array $snapshot) use ($coupons): array {
            $coupon = $snapshot['coupon_id']
                ? $coupons->firstWhere('id', $snapshot['coupon_id'])
                : $coupons->first(fn (Coupon $coupon): bool => mb_strtolower($coupon->code) === mb_strtolower($snapshot['code'] ?? ''));

            return ['coupon' => $coupon, 'snapshot' => $snapshot];
        })->filter(fn (array $entry): bool => $entry['coupon'] instanceof Coupon)
            ->unique(fn (array $entry): int => $entry['coupon']->id)
            // The ordinary coupon always runs first, even if entered after a stackable one.
            ->sortBy(fn (array $entry): int => $entry['coupon']->is_stackable ? 1 : 0);

        $this->applyCouponDiscountToCartItemsAction->execute($cart);
        $applied = [];
        $itemDiscounts = [];
        $hasOrdinaryCoupon = false;

        foreach ($entries as $entry) {
            $coupon = $entry['coupon'];
            if (! $coupon->is_stackable && $hasOrdinaryCoupon) {
                continue;
            }

            try {
                $result = $this->calculateCouponDiscountForCartAction->execute(
                    cart: $cart, coupon: $coupon, user: $user ?? $cart->user, useRemainingAmount: true,
                );
            } catch (DomainException $exception) {
                if ((int) $coupon->id === $requiredCouponId) {
                    throw $exception;
                }

                continue;
            }

            foreach ($result->itemDiscounts as $itemId => $discount) {
                $itemDiscounts[$itemId] = ($itemDiscounts[$itemId] ?? 0) + $discount;
            }
            $this->applyCouponDiscountToCartItemsAction->execute($cart, $itemDiscounts);
            $hasOrdinaryCoupon = $hasOrdinaryCoupon || ! $coupon->is_stackable;
            $applied[] = [
                'coupon_id' => (int) $coupon->id,
                'code' => $coupon->code,
                'eligible_amount' => $result->eligibleAmount,
                'discount_amount' => $result->discountAmount,
                'applied_at' => $entry['snapshot']['applied_at'] ?? now()->toDateTimeString(),
            ];
        }

        if ($requiredCouponId !== null && ! collect($applied)->contains('coupon_id', $requiredCouponId)) {
            throw new DomainException('Coupon is not currently usable.');
        }

        return $applied;
    }
}
