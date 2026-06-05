<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Catalog\Models\ProductAddon;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddProductSaleItemWithAddonsToCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction $resolveCurrentCartOwnerAction,
        private readonly GetOrCreateActiveCartAction   $getOrCreateActiveCartAction,
        private readonly AddPurchasableToCartAction    $addPurchasableToCartAction,
        private readonly RecalculateCartTotalsAction   $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Add selected product sale item with selected addons to current cart.
     *
     * @param array<int, int|string> $addonIds
     * @throws Throwable
     */
    public function execute(
        int           $productSaleItemId,
        array         $addonIds = [],
        int           $quantity = 1,
        ?CartOwnerDTO $owner = null,
    ): Cart
    {
        return DB::transaction(function () use ($productSaleItemId, $addonIds, $quantity, $owner) {
            $owner ??= $this->resolveCurrentCartOwnerAction->execute();

            $cart = $this->getOrCreateActiveCartAction->execute($owner);

            $saleItem = ProductSaleItem::query()
                ->with('product')
                ->whereKey($productSaleItemId)
                ->firstOrFail();

            $addons = $this->getValidAddons($saleItem, $addonIds);

            $this->removeExistingProductItemsFromCart(
                cart: $cart,
                saleItem: $saleItem,
            );

            $mainCartItem = $this->addPurchasableToCartAction->execute(
                purchasable: $saleItem,
                quantity: $quantity,
                parentCartItem: null,
                metadata: [
                    'product_id' => $saleItem->product_id,
                    'kind' => 'product_sale_item',
                ],
                owner: $owner,
            );

            foreach ($addons as $addon) {
                $this->addPurchasableToCartAction->execute(
                    purchasable: $addon,
                    quantity: 1,
                    parentCartItem: $mainCartItem,
                    metadata: [
                        'product_id' => $addon->product_id,
                        'kind' => 'product_addon',
                    ],
                    owner: $owner,
                );
            }

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }

    /**
     * @param array<int, int|string> $addonIds
     * @return Collection<int, ProductAddon>
     */
    private function getValidAddons(ProductSaleItem $saleItem, array $addonIds): Collection
    {
        $normalizedAddonIds = collect($addonIds)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        if ($normalizedAddonIds->isEmpty()) {
            return collect();
        }

        $addons = ProductAddon::query()
            ->whereIn('id', $normalizedAddonIds)
            ->where('product_id', $saleItem->product_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($addons->count() !== $normalizedAddonIds->count()) {
            throw new DomainException('Some selected addons are invalid.');
        }

        return $addons;
    }

    /**
     * Remove previous cart items of the same product.
     *
     * This keeps product purchase UX clean: selecting another variant replaces the old one.
     */
    private function removeExistingProductItemsFromCart(Cart $cart, ProductSaleItem $saleItem): void
    {
        $rootItems = CartItem::query()
            ->where('cart_id', $cart->id)
            ->whereNull('parent_cart_item_id')
            ->where('purchasable_type', $saleItem->getMorphClass())
            ->where('metadata->product_id', $saleItem->product_id)
            ->get();

        foreach ($rootItems as $rootItem) {
            $rootItem->delete();
        }
    }
}
