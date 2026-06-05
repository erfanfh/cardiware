<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\Cart;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClearCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction  $resolveCurrentCartOwnerAction,
        private readonly EnsureCartBelongsToOwnerAction $ensureCartBelongsToOwnerAction,
        private readonly RecalculateCartTotalsAction    $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Clear all items from the cart.
     *
     * @throws Throwable
     */
    public function execute(Cart $cart, ?CartOwnerDTO $owner = null): Cart
    {
        return DB::transaction(function () use ($cart, $owner) {
            $owner ??= $this->resolveCurrentCartOwnerAction->execute();

            $this->ensureCartBelongsToOwnerAction->execute($owner, $cart);

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cart->items()
                ->whereNotNull('parent_cart_item_id')
                ->delete();

            $cart->items()
                ->whereNull('parent_cart_item_id')
                ->delete();

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}
