<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveCartItemAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction  $resolveCurrentCartOwnerAction,
        private readonly EnsureCartBelongsToOwnerAction $ensureCartBelongsToOwnerAction,
        private readonly RecalculateCartTotalsAction    $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Remove an item from the cart.
     *
     * @throws Throwable
     */
    public function execute(CartItem $cartItem, ?CartOwnerDTO $owner = null): Cart
    {
        return DB::transaction(function () use ($cartItem, $owner) {
            $owner ??= $this->resolveCurrentCartOwnerAction->execute();

            $cart = $cartItem->cart;

            $this->ensureCartBelongsToOwnerAction->execute($owner, $cart);

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cartItem->delete();

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}
