<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveProductFromCartAction
{
    public function __construct(
        private readonly GetCurrentActiveCartAction  $getCurrentActiveCartAction,
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Remove product root item and its dependent items from current cart.
     *
     * @throws Throwable
     */
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $cart = $this->getCurrentActiveCartAction->execute();

            if ($cart === null) {
                return;
            }

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $rootItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->whereNull('parent_cart_item_id')
                ->where('purchasable_type', app(ProductSaleItem::class)->getMorphClass())
                ->where('metadata->product_id', $product->id)
                ->latest('id')
                ->first();

            if (!$rootItem instanceof CartItem) {
                return;
            }

            $rootItem->delete();

            $cart->refresh();

            $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}
