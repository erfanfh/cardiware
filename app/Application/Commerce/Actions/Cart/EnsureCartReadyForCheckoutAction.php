<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Exceptions\CartNeedsRefreshException;
use App\Domain\Commerce\Models\Cart;
use DomainException;
use Throwable;

class EnsureCartReadyForCheckoutAction
{
    public function __construct(
        private readonly SyncCartItemsAction $syncCartItemsAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Cart $cart): Cart
    {
        $hadCoupon = filled($cart->coupon_id) || filled($cart->coupon_code);

        $result = $this->syncCartItemsAction->execute($cart);

        $cart = $result['cart']->refresh();

        if ((bool)($result['has_changes'] ?? false)) {
            throw new CartNeedsRefreshException($result);
        }

        if ($hadCoupon && blank($cart->coupon_id) && blank($cart->coupon_code)) {
            throw new DomainException('Applied coupon is no longer valid.');
        }

        if ($cart->items()->count() < 1) {
            throw new DomainException('Cart is empty.');
        }

        if (!$cart->isEditable()) {
            throw new DomainException('Cart is not editable.');
        }

        return $cart->refresh();
    }
}
