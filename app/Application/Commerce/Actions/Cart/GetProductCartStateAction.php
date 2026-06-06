<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\CartItem;

class GetProductCartStateAction
{
    public function __construct(
        private readonly GetCurrentActiveCartAction $getCurrentActiveCartAction,
    )
    {
    }

    /**
     * Get current product state inside active cart.
     *
     * @return array<string, mixed>
     */
    public function execute(Product $product): array
    {
        $cart = $this->getCurrentActiveCartAction->execute();

        if ($cart === null) {
            return $this->emptyState();
        }

        $rootItem = CartItem::query()
            ->with(['children'])
            ->where('cart_id', $cart->id)
            ->whereNull('parent_cart_item_id')
            ->where('purchasable_type', app(ProductSaleItem::class)->getMorphClass())
            ->where('metadata->product_id', $product->id)
            ->latest('id')
            ->first();

        if (!$rootItem instanceof CartItem) {
            return $this->emptyState();
        }

        $purchasable = $rootItem->purchasable;

        $maxQuantity = method_exists($purchasable, 'getMaxQuantityPerOrder')
            ? $purchasable->getMaxQuantityPerOrder()
            : 1;

        return [
            'in_cart' => true,
            'cart_item_id' => $rootItem->id,
            'sale_item_id' => $rootItem->purchasable_id,
            'quantity' => $rootItem->quantity,
            'max_quantity_per_order' => $maxQuantity,
            'can_change_quantity' => $maxQuantity === null || $maxQuantity > 1,
            'selected_addon_ids' => $rootItem->children
                ->pluck('purchasable_id')
                ->map(fn($id) => (int)$id)
                ->values()
                ->all(),
            'item_total_amount' => $rootItem->total_amount + $rootItem->children->sum('total_amount'),
            'cart' => [
                'id' => $cart->id,
                'items_count' => $cart->items()
                    ->whereNull('parent_cart_item_id')
                    ->count(),
                'subtotal_amount' => $cart->subtotal_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyState(): array
    {
        return [
            'in_cart' => false,
            'cart_item_id' => null,
            'sale_item_id' => null,
            'quantity' => 1,
            'max_quantity_per_order' => null,
            'can_change_quantity' => false,
            'selected_addon_ids' => [],
            'item_total_amount' => 0,
            'cart' => null,
        ];
    }
}
