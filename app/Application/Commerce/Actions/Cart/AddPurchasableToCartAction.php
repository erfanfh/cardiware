<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddPurchasableToCartAction
{
    public function __construct(
        private readonly GetOrCreateActiveCartAction      $getOrCreateActiveCartAction,
        private readonly ValidatePurchasableForCartAction $validatePurchasableForCartAction,
        private readonly FindMatchingCartItemAction       $findMatchingCartItemAction,
        private readonly CalculateCartItemQuantityAction  $calculateCartItemQuantityAction,
        private readonly CreateCartItemAction             $createCartItemAction,
        private readonly UpdateCartItemAction             $updateCartItemAction,
        private readonly RecalculateCartItemTotalsAction  $recalculateCartItemTotalsAction,
        private readonly RecalculateCartTotalsAction      $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Add a purchasable item to the user's active cart.
     *
     * @param Purchasable&Model $purchasable
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        int               $userId,
        Purchasable&Model $purchasable,
        int               $quantity = 1,
        ?CartItem         $parentCartItem = null,
        array             $metadata = [],
    ): CartItem
    {
        return DB::transaction(function () use ($userId, $purchasable, $quantity, $parentCartItem, $metadata) {
            $cart = $this->getOrCreateActiveCartAction->execute($userId);

            $this->validatePurchasableForCartAction->execute(
                userId: $userId,
                cart: $cart,
                purchasable: $purchasable,
                quantity: $quantity,
                parentCartItem: $parentCartItem,
            );

            $cartItem = $this->findMatchingCartItemAction->execute(
                cart: $cart,
                purchasable: $purchasable,
                parentCartItem: $parentCartItem,
            );

            $finalQuantity = $this->calculateCartItemQuantityAction->execute(
                purchasable: $purchasable,
                requestedQuantity: $quantity,
                currentCartItem: $cartItem,
            );

            if ($cartItem instanceof CartItem) {
                $cartItem = $this->updateCartItemAction->execute(
                    cartItem: $cartItem,
                    purchasable: $purchasable,
                    quantity: $finalQuantity,
                    metadata: $metadata,
                );
            } else {
                $cartItem = $this->createCartItemAction->execute(
                    cart: $cart,
                    purchasable: $purchasable,
                    quantity: $finalQuantity,
                    parentCartItem: $parentCartItem,
                    metadata: $metadata,
                );
            }

            $cartItem = $this->recalculateCartItemTotalsAction->execute($cartItem);

            $this->recalculateCartTotalsAction->execute($cart);

            return $cartItem->refresh();
        });
    }
}
