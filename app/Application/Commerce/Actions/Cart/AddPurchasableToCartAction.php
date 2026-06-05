<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddPurchasableToCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction    $resolveCurrentCartOwnerAction,
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
     * Add a purchasable item to the current active cart.
     *
     * @param Purchasable&Model $purchasable
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        Purchasable&Model $purchasable,
        int               $quantity = 1,
        ?CartItem         $parentCartItem = null,
        array             $metadata = [],
        ?CartOwnerDTO     $owner = null,
    ): CartItem
    {
        return DB::transaction(function () use ($purchasable, $quantity, $parentCartItem, $metadata, $owner) {
            $owner ??= $this->resolveCurrentCartOwnerAction->execute();

            $cart = $this->getOrCreateActiveCartAction->execute($owner);

            $this->validatePurchasableForCartAction->execute(
                owner: $owner,
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
