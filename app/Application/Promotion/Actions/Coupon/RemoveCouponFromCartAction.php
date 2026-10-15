<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Application\Commerce\Actions\Cart\RecalculateCartTotalsAction;
use App\Domain\Commerce\Models\Cart;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveCouponFromCartAction
{
    public function __construct(
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    ) {}

    /**
     * Omit the code to preserve the existing remove-all behavior.
     *
     * @throws Throwable
     */
    public function execute(Cart $cart, ?string $code = null): Cart
    {
        return DB::transaction(function () use ($cart, $code): Cart {
            $cart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if (! $cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cart->applied_coupons = $code === null ? [] : collect($cart->appliedCouponSnapshots())
                ->reject(fn (array $snapshot): bool => mb_strtolower($snapshot['code'] ?? '') === mb_strtolower(trim($code)))
                ->values()->all();

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}
