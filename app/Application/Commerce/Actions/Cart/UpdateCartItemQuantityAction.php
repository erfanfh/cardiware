<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateCartItemQuantityAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction   $resolveCurrentCartOwnerAction,
        private readonly EnsureCartBelongsToOwnerAction  $ensureCartBelongsToOwnerAction,
        private readonly RecalculateCartItemTotalsAction $recalculateCartItemTotalsAction,
        private readonly RecalculateCartTotalsAction     $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Update cart item quantity.
     *
     * @throws Throwable
     */
    public function execute(CartItem $cartItem, int $quantity, ?CartOwnerDTO $owner = null): CartItem
    {
        return DB::transaction(function () use ($cartItem, $quantity, $owner) {
            $owner ??= $this->resolveCurrentCartOwnerAction->execute();

            $cart = $cartItem->cart;

            $this->validate($owner, $cart, $cartItem, $quantity);

            $cartItem->forceFill([
                'quantity' => $quantity,
            ])->save();

            $cartItem = $this->recalculateCartItemTotalsAction->execute($cartItem);

            $this->recalculateCartTotalsAction->execute($cart);

            return $cartItem->refresh();
        });
    }

    /**
     * Validate quantity update.
     */
    private function validate(CartOwnerDTO $owner, Cart $cart, CartItem $cartItem, int $quantity): void
    {
        $this->ensureCartBelongsToOwnerAction->execute($owner, $cart);

        if (!$cart->isEditable()) {
            throw new DomainException('Cart is not editable.');
        }

        if ($quantity < 1) {
            throw new DomainException('Quantity must be at least 1.');
        }

        if ($cartItem->isChild() && $quantity !== 1) {
            throw new DomainException('Dependent cart items cannot have quantity greater than 1.');
        }

        $purchasable = $cartItem->purchasable;

        if (!$purchasable instanceof Model || !$purchasable instanceof Purchasable) {
            throw new DomainException('Cart item has invalid purchasable model.');
        }

        $maxQuantityPerOrder = $purchasable->getMaxQuantityPerOrder();

        if ($maxQuantityPerOrder !== null && $quantity > $maxQuantityPerOrder) {
            throw new DomainException('Requested quantity exceeds the allowed maximum per order.');
        }
    }
}
