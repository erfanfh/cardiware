<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class ValidatePurchasableForCartAction
{
    public function __construct(
        private readonly ValidatePurchasableDependencyAction $validatePurchasableDependencyAction,
        private readonly EnsureCartBelongsToOwnerAction      $ensureCartBelongsToOwnerAction,
    )
    {
    }

    /**
     * Validate a purchasable item before adding it to cart.
     *
     * @param Purchasable&Model $purchasable
     */
    public function execute(
        CartOwnerDTO      $owner,
        Cart              $cart,
        Purchasable&Model $purchasable,
        int               $quantity,
        ?CartItem         $parentCartItem = null,
    ): void
    {
        $this->ensureCartBelongsToOwnerAction->execute($owner, $cart);
        $this->validateCartIsEditable($cart);
        $this->validateQuantity($quantity);
        $this->validatePurchasable($purchasable);
        $this->validateParentRules($cart, $purchasable, $parentCartItem);
    }

    private function validateCartIsEditable(Cart $cart): void
    {
        if (!$cart->isEditable()) {
            throw new DomainException('Cart is not editable.');
        }
    }

    private function validateQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw new DomainException('Quantity must be at least 1.');
        }
    }

    private function validatePurchasable(Purchasable $purchasable): void
    {
        if (!$purchasable->isPurchasable()) {
            throw new DomainException('This item is not purchasable.');
        }
    }

    /**
     * @param Purchasable&Model $purchasable
     */
    private function validateParentRules(
        Cart              $cart,
        Purchasable&Model $purchasable,
        ?CartItem         $parentCartItem
    ): void
    {
        if (!$purchasable->isDependentPurchasable()) {
            if ($parentCartItem !== null) {
                throw new DomainException('Independent purchasable items cannot have parent cart items.');
            }

            return;
        }

        if ($parentCartItem === null) {
            throw new DomainException('Dependent purchasable items require a parent cart item.');
        }

        if ($parentCartItem->isChild()) {
            throw new DomainException('Dependent purchasable items cannot be attached to another dependent item.');
        }

        if ((int)$parentCartItem->cart_id !== (int)$cart->id) {
            throw new DomainException('Parent cart item does not belong to the active cart.');
        }

        $this->validatePurchasableDependencyAction->execute(
            purchasable: $purchasable,
            parentCartItem: $parentCartItem,
        );
    }
}
