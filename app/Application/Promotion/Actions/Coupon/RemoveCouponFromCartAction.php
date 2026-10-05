<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Application\Commerce\Actions\Cart\RecalculateCartItemTotalsAction;
use App\Application\Commerce\Support\TaxCalculator;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveCouponFromCartAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction      $recalculateCartItemTotalsAction,
        private readonly ApplyCouponDiscountToCartItemsAction $applyCouponDiscountToCartItemsAction,
        private readonly TaxCalculator                         $taxCalculator,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Cart $cart): Cart
    {
        return DB::transaction(function () use ($cart): Cart {
            /** @var Cart $cart */
            $cart = Cart::query()
                ->whereKey($cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cart->loadMissing([
                'items.purchasable',
            ]);

            foreach ($cart->items as $item) {
                if ($item instanceof CartItem) {
                    $this->recalculateCartItemTotalsAction->execute($item);
                }
            }

            $this->applyCouponDiscountToCartItemsAction->execute(
                cart: $cart,
                itemDiscounts: [],
            );

            $items = $cart->items()->get();

            $subtotalAmount = (int)$items->sum('subtotal_amount');
            $productDiscountAmount = (int)$items->sum('discount_amount');
            $tax = $this->taxCalculator->calculate((int)$items->sum('final_total_amount'));

            $cart->forceFill([
                'coupon_id' => null,
                'coupon_code' => null,
                'coupon_eligible_amount' => 0,
                'coupon_discount_amount' => 0,
                'coupon_applied_at' => null,

                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $productDiscountAmount,
                'taxable_amount' => $tax->taxableAmount,
                'tax_rate_basis_points' => $tax->rateBasisPoints,
                'tax_amount' => $tax->taxAmount,
                'total_amount' => $tax->totalAmount,
            ])->save();

            return $cart->refresh();
        });
    }
}
