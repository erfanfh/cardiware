<?php

namespace App\Livewire\Front\Catalog;

use App\Application\Commerce\Actions\Cart\AddProductSaleItemWithAddonsToCartAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use DomainException;
use Livewire\Component;

class ProductBuyBox extends Component
{
    public Product $product;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $saleItems = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $addons = [];

    public function mount(Product $product): void
    {
        $this->product = $product;

        $this->saleItems = $this->makeSaleItemsData();

        $this->addons = $this->makeAddonsData();
    }

    /**
     * Add selected product sale item with selected addons to cart.
     *
     * @param array<int, int|string> $addonIds
     * @return array<string, mixed>
     */
    public function addToCart(
        AddProductSaleItemWithAddonsToCartAction $action,
        int                                      $saleItemId,
        array                                    $addonIds = [],
    ): array
    {
        try {
            $cart = $action->execute(
                productSaleItemId: $saleItemId,
                addonIds: $addonIds,
                quantity: 1,
            );

            $cart->loadMissing('items');

            $summary = [
                'id' => $cart->id,
                'items_count' => $cart->items()
                    ->whereNull('parent_cart_item_id')
                    ->count(),
                'subtotal_amount' => $cart->subtotal_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,
            ];

            $this->dispatch('cart-updated', cart: $summary);

            return [
                'success' => true,
                'message' => 'محصول با موفقیت به سبد خرید اضافه شد.',
                'cart' => $summary,
            ];
        } catch (DomainException $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.front.catalog.product-buy-box');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function makeSaleItemsData(): array
    {
        return $this->product
            ->saleItems()
            ->with([
                'product',
                'attributeValues.globalValue.attribute',
                'attributeValues.customValue.customAttribute',
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function (ProductSaleItem $saleItem) {
                return [
                    'id' => $saleItem->id,
                    'title' => $this->makeSaleItemTitle($saleItem),
                    'description' => $saleItem->description,
                    'price' => (int)$saleItem->base_price,
                    'status' => $saleItem->status instanceof \BackedEnum
                        ? $saleItem->status->value
                        : $saleItem->status,
                    'max_quantity_per_order' => $saleItem->max_quantity_per_order,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function makeAddonsData(): array
    {
        return $this->product
            ->addons()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($addon) => [
                'id' => $addon->id,
                'title' => $addon->title,
                'description' => $addon->description,
                'price' => (int)$addon->price,
                'max_quantity_per_order' => (int)$addon->max_quantity_per_order,
            ])
            ->values()
            ->all();
    }

    private function makeSaleItemTitle(ProductSaleItem $saleItem): string
    {
        $labels = $saleItem->attributeValues
            ->map(function ($selectedValue) {
                if ($selectedValue->globalValue) {
                    return $selectedValue->globalValue->attribute?->title . ': ' . $selectedValue->globalValue->title;
                }

                if ($selectedValue->customValue) {
                    return $selectedValue->customValue->customAttribute?->title . ': ' . $selectedValue->customValue->title;
                }

                return null;
            })
            ->filter()
            ->values();

        if ($labels->isNotEmpty()) {
            return $labels->implode(' / ');
        }

        return 'نسخه اصلی محصول';
    }
}
