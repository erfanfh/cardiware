<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class ValidatePurchasableDependencyAction
{
    /**
     * Validate dependency rules for dependent purchasable items.
     *
     * @param Purchasable&Model $purchasable
     */
    public function execute(
        Purchasable&Model $purchasable,
        CartItem          $parentCartItem,
    ): void
    {
        if (!$purchasable->isDependentPurchasable()) {
            return;
        }

        $dependency = $purchasable->getPurchasableDependency();

        if ($dependency === null) {
            throw new DomainException('Dependent purchasable item must provide dependency data.');
        }

        $parentPurchasable = $parentCartItem->purchasable;

        if (!$parentPurchasable instanceof Model) {
            throw new DomainException('Parent cart item has invalid purchasable model.');
        }

        $this->validateRequiredParentType($dependency, $parentPurchasable);
        $this->validateSameProduct($dependency, $parentPurchasable);
    }

    /**
     * Validate required parent morph type.
     *
     * @param array<string, mixed> $dependency
     */
    private function validateRequiredParentType(array $dependency, Model $parentPurchasable): void
    {
        $requiredParentType = $dependency['required_parent_type'] ?? null;

        if ($requiredParentType === null) {
            return;
        }

        if ($parentPurchasable->getMorphClass() !== $requiredParentType) {
            throw new DomainException('Dependent purchasable item has invalid parent type.');
        }
    }

    /**
     * Validate that child and parent belong to the same product.
     *
     * @param array<string, mixed> $dependency
     */
    private function validateSameProduct(array $dependency, Model $parentPurchasable): void
    {
        $requiredProductId = $dependency['product_id'] ?? null;

        if ($requiredProductId === null) {
            return;
        }

        if (!isset($parentPurchasable->product_id)) {
            throw new DomainException('Parent purchasable item does not provide product dependency.');
        }

        if ((int)$parentPurchasable->product_id !== (int)$requiredProductId) {
            throw new DomainException('Dependent purchasable item does not belong to the parent product.');
        }
    }
}
