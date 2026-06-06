<?php

namespace App\Livewire\Front\Catalog;

use App\Application\Commerce\Actions\Cart\AddProductSaleItemWithAddonsToCartAction;
use App\Application\Commerce\Actions\Cart\GetProductCartStateAction;
use App\Application\Commerce\Actions\Cart\RemoveProductFromCartAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use DomainException;
use Livewire\Component;
use Throwable;

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

    /**
     * @var array<string, mixed>
     */
    public array $purchaseState = [];

    public function mount(
        Product                   $product,
        GetProductCartStateAction $getProductCartStateAction,
    ): void
    {
        $this->product = $product;

        $this->saleItems = $this->makeSaleItemsData();
        $this->addons = $this->makeAddonsData();

        $this->purchaseState = $getProductCartStateAction->execute($product);
    }

    /**
     * Add or update selected product sale item with selected addons.
     *
     * @param array<int, int|string> $addonIds
     * @return array<string, mixed>
     * @throws Throwable
     */
    public function addToCart(
        AddProductSaleItemWithAddonsToCartAction $action,
        GetProductCartStateAction                $getProductCartStateAction,
        int                                      $saleItemId,
        array                                    $addonIds = [],
        int                                      $quantity = 1,
    ): array
    {
        try {
            $cart = $action->execute(
                productSaleItemId: $saleItemId,
                addonIds: $addonIds,
                quantity: $quantity,
            );

            $state = $getProductCartStateAction->execute($this->product);

            $this->purchaseState = $state;

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
                'message' => 'سبد خرید با موفقیت به‌روزرسانی شد.',
                'state' => $state,
                'cart' => $summary,
            ];
        } catch (DomainException $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'خطایی هنگام به‌روزرسانی سبد خرید رخ داد.',
            ];
        }
    }

    /**
     * Remove current product from cart.
     *
     * @return array<string, mixed>
     */
    public function removeFromCart(
        RemoveProductFromCartAction $removeProductFromCartAction,
        GetProductCartStateAction   $getProductCartStateAction,
    ): array
    {
        try {
            $removeProductFromCartAction->execute($this->product);

            $state = $getProductCartStateAction->execute($this->product);

            $this->purchaseState = $state;

            $this->dispatch('cart-updated');

            return [
                'success' => true,
                'message' => 'محصول از سبد خرید حذف شد.',
                'state' => $state,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'خطایی هنگام حذف محصول از سبد خرید رخ داد.',
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
