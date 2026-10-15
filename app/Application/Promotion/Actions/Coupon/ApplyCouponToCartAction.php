<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Application\Commerce\Actions\Cart\RecalculateCartTotalsAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApplyCouponToCartAction
{
    public function __construct(
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(Cart $cart, string $code, ?User $user = null): Cart
    {
        $code = trim($code);
        if ($code === '') {
            throw new DomainException('Coupon code is required.');
        }

        return DB::transaction(function () use ($cart, $code, $user): Cart {
            $cart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if (! $cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $coupon = Coupon::query()
                ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
                ->first();
            if (! $coupon) {
                throw new DomainException('Coupon not found.');
            }

            $snapshots = collect($cart->appliedCouponSnapshots());
            // Lock all involved coupons in a consistent order for concurrent checkouts.
            $existingCoupons = Coupon::query()
                ->where(function ($query) use ($snapshots, $coupon) {
                    $query->whereIn('id', [...$snapshots->pluck('coupon_id')->filter()->all(), $coupon->id]);
                    $codes = $snapshots->whereNull('coupon_id')->pluck('code')->filter()
                        ->map(fn (string $code): string => mb_strtolower($code))->all();
                    if ($codes !== []) {
                        $query->orWhereIn(DB::raw('LOWER(code)'), $codes);
                    }
                })->orderBy('id')->lockForUpdate()->get();
            $coupon = $existingCoupons->firstWhere('id', $coupon->id);
            if (! $coupon || mb_strtolower($coupon->code) !== mb_strtolower($code)) {
                throw new DomainException('Coupon not found.');
            }

            $snapshots = $snapshots->filter(function (array $snapshot) use ($coupon, $existingCoupons): bool {
                $existing = $snapshot['coupon_id']
                    ? $existingCoupons->firstWhere('id', $snapshot['coupon_id'])
                    : $existingCoupons->first(fn (Coupon $existing): bool => mb_strtolower($existing->code) === mb_strtolower($snapshot['code'] ?? ''));

                return $existing && ($coupon->is_stackable || $existing->is_stackable || $existing->id === $coupon->id);
            })->values();

            if (! $snapshots->contains(fn (array $snapshot): bool => (int) ($snapshot['coupon_id'] ?? 0) === (int) $coupon->id
                || mb_strtolower($snapshot['code'] ?? '') === mb_strtolower($coupon->code))) {
                $snapshots->push([
                    'coupon_id' => (int) $coupon->id,
                    'code' => $coupon->code,
                    'eligible_amount' => 0,
                    'discount_amount' => 0,
                    'applied_at' => now()->toDateTimeString(),
                ]);
            }

            $cart->applied_coupons = $snapshots->all();

            // Failure of the requested code rolls back all changes, including item totals.
            return $this->recalculateCartTotalsAction->execute($cart, (int) $coupon->id, $user);
        });
    }
}
