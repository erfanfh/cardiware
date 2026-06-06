<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Actions\Cart\ValidatePurchasableDependencyAction;
use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\Exceptions\CartItemPriceChangedException;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class ValidateCartItemsForCheckoutAction
{
    public function __construct(
        private readonly ValidatePurchasableDependencyAction $validatePurchasableDependencyAction,
    )
    {
    }

    /**
     * Validate all cart items before creating an order.
     */
    public function execute(Cart $cart): void
    {
        $cart->loadMissing([
            'items.parent',
            'items.purchasable',
        ]);

        foreach ($cart->items as $cartItem) {
            $this->validateCartItem($cartItem);
        }
    }

    /**
     * Validate a single cart item.
     */
    private function validateCartItem(CartItem $cartItem): void
    {
        $purchasable = $cartItem->purchasable;

        if (!$purchasable instanceof Model || !$purchasable instanceof Purchasable) {
            throw new DomainException('Cart item has invalid purchasable model.');
        }

        $this->validatePurchasable($purchasable);
        $this->validateQuantity($cartItem, $purchasable);
        $this->validatePrice($cartItem, $purchasable);
        $this->validateDependency($cartItem, $purchasable);
    }

    /**
     * Validate purchasable status.
     */
    private function validatePurchasable(Purchasable $purchasable): void
    {
        if (!$purchasable->isPurchasable()) {
            throw new DomainException('One or more cart items are no longer purchasable.');
        }
    }

    /**
     * Validate cart item quantity against purchasable rules.
     *
     * @param Purchasable&Model $purchasable
     */
    private function validateQuantity(CartItem $cartItem, Purchasable&Model $purchasable): void
    {
        if ($cartItem->quantity < 1) {
            throw new DomainException('Cart item quantity is invalid.');
        }

        $maxQuantityPerOrder = $purchasable->getMaxQuantityPerOrder();

        if ($maxQuantityPerOrder !== null && $cartItem->quantity > $maxQuantityPerOrder) {
            throw new DomainException('One or more cart items exceed the allowed maximum quantity.');
        }
    }

    /**
     * Validate cart item price against current purchasable price.
     *
     * @param Purchasable&Model $purchasable
     */
    private function validatePrice(CartItem $cartItem, Purchasable&Model $purchasable): void
    {
        $currentPrice = $purchasable->getPurchasablePrice();

        if ((int)$cartItem->unit_price !== (int)$currentPrice) {
            throw new CartItemPriceChangedException();
        }
    }

    /**
     * Validate dependent cart item rules.
     *
     * @param Purchasable&Model $purchasable
     */
    private function validateDependency(CartItem $cartItem, Purchasable&Model $purchasable): void
    {
        if (!$purchasable->isDependentPurchasable()) {
            if ($cartItem->parent_cart_item_id !== null) {
                throw new DomainException('Independent cart item cannot have a parent item.');
            }

            return;
        }

        if ($cartItem->parent_cart_item_id === null) {
            throw new DomainException('Dependent cart item must have a parent item.');
        }

        $parentCartItem = $cartItem->parent;

        if (!$parentCartItem instanceof CartItem) {
            throw new DomainException('Dependent cart item has invalid parent item.');
        }

        if ((int)$parentCartItem->cart_id !== (int)$cartItem->cart_id) {
            throw new DomainException('Dependent cart item parent does not belong to the same cart.');
        }

        if ($parentCartItem->parent_cart_item_id !== null) {
            throw new DomainException('Dependent cart item cannot be attached to another dependent item.');
        }

        $this->validatePurchasableDependencyAction->execute(
            purchasable: $purchasable,
            parentCartItem: $parentCartItem,
        );
    }
}
