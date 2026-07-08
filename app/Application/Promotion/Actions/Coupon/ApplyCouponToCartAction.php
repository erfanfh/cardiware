<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Application\Commerce\Actions\Cart\RecalculateCartItemTotalsAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApplyCouponToCartAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction      $recalculateCartItemTotalsAction,
        private readonly CalculateCouponDiscountForCartAction $calculateCouponDiscountForCartAction,
        private readonly ApplyCouponDiscountToCartItemsAction $applyCouponDiscountToCartItemsAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Cart $cart, string $code, ?User $user = null): Cart
    {
        $code = $this->normalizeCode($code);

        if ($code === '') {
            throw new DomainException('Coupon code is required.');
        }

        return DB::transaction(function () use ($cart, $code, $user): Cart {
            /** @var Cart $cart */
            $cart = Cart::query()
                ->whereKey($cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $coupon = Coupon::query()
                ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
                ->with([
                    'targets',
                    'couponUsers',
                ])
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                throw new DomainException('Coupon not found.');
            }

            $cart->loadMissing([
                'items.purchasable',
            ]);

            foreach ($cart->items as $item) {
                if ($item instanceof CartItem) {
                    $this->recalculateCartItemTotalsAction->execute($item);
                }
            }

            $cart->load([
                'items.purchasable',
                'items.purchasable.product',
                'items.parent.purchasable',
                'items.parent.purchasable.product',
                'user',
            ]);

            $result = $this->calculateCouponDiscountForCartAction->execute(
                cart: $cart,
                coupon: $coupon,
                user: $user,
            );

            $this->applyCouponDiscountToCartItemsAction->execute(
                cart: $cart,
                itemDiscounts: $result->itemDiscounts,
            );

            $items = $cart->items()->get();

            $subtotalAmount = (int)$items->sum('subtotal_amount');
            $productDiscountAmount = (int)$items->sum('discount_amount');
            $allocatedCouponDiscountAmount = (int)$items->sum('coupon_discount_amount');

            $discountAmount = $productDiscountAmount + $allocatedCouponDiscountAmount;
            $totalAmount = (int)$items->sum('final_total_amount');

            $cart->forceFill([
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'coupon_eligible_amount' => $result->eligibleAmount,
                'coupon_discount_amount' => $allocatedCouponDiscountAmount,
                'coupon_applied_at' => now(),

                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => max(0, $totalAmount),
            ])->save();

            return $cart->refresh();
        });
    }

    private function normalizeCode(string $code): string
    {
        return trim($code);
    }
}
