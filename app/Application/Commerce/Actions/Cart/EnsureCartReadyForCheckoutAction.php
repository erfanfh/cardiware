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
        $result = $this->syncCartItemsAction->execute($cart);

        $cart = $result['cart'];

        if ((bool)($result['has_changes'] ?? false)) {
            throw new CartNeedsRefreshException($result);
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
