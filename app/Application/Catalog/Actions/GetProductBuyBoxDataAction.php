<?php

namespace App\Application\Catalog\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Support\Facades\Cache;

final class GetProductBuyBoxDataAction
{
    /**
     * @return array{
     *     saleItems: array<int, array<string, mixed>>,
     *     addons: array<int, array<string, mixed>>,
     *     hasAvailableSaleItems: bool
     * }
     */
    public function execute(Product $product): array
    {
        return Cache::tags([
            CacheTag::PRODUCTS,
            CacheTag::PRODUCT_DETAIL,
            CacheTag::PRODUCT_BUY_BOX,
            CacheTag::product($product->getKey()),
        ])->remember(
            CacheKey::productBuyBoxData(
                productId: $product->getKey(),
                productUpdatedAt: $product->updated_at?->timestamp ?? 0,
            ),
            CacheTtl::long(),
            fn() => $this->resolve($product)
        );
    }

    /**
     * @return array{
     *     saleItems: array<int, array<string, mixed>>,
     *     addons: array<int, array<string, mixed>>,
     *     hasAvailableSaleItems: bool
     * }
     */
    private function resolve(Product $product): array
    {
        $saleItems = $this->saleItems($product);
        $addons = $this->addons($product);

        return [
            'saleItems' => $saleItems,
            'addons' => $addons,
            'hasAvailableSaleItems' => count($saleItems) > 0,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function saleItems(Product $product): array
    {
        return $product
            ->saleItems()
            ->where('status', ProductSaleItemStatusEnum::AVAILABLE)
            ->with([
                'attributeValues.globalValue.attribute',
                'attributeValues.customValue.customAttribute',
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn(ProductSaleItem $saleItem) => [
                'id' => $saleItem->id,
                'title' => $this->makeSaleItemTitle($saleItem),
                'description' => $saleItem->description,
                'price' => (int)$saleItem->base_price,
                'status' => $saleItem->status instanceof \BackedEnum
                    ? $saleItem->status->value
                    : $saleItem->status,
                'max_quantity_per_order' => $saleItem->max_quantity_per_order,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function addons(Product $product): array
    {
        return $product
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
