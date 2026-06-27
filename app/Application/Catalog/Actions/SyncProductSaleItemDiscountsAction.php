<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use Illuminate\Support\Collection;

class SyncProductSaleItemDiscountsAction
{
    /**
     * Sync sale item discount data.
     *
     * @param array<int, array<string, mixed>> $saleItems
     */
    public function execute(Product $product, array $saleItems): void
    {
        $product->load([
            'saleItems' => fn($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
            'saleItems.discount',
        ]);

        $saleItemsById = $product->saleItems
            ->keyBy(fn(ProductSaleItem $saleItem) => (int)$saleItem->id);

        $saleItemsBySku = $product->saleItems
            ->filter(fn(ProductSaleItem $saleItem) => filled($saleItem->sku))
            ->keyBy(fn(ProductSaleItem $saleItem) => (string)$saleItem->sku);

        $saleItemsByIndex = $product->saleItems->values();

        foreach (array_values($saleItems) as $index => $itemData) {
            if (!is_array($itemData)) {
                continue;
            }

            $saleItem = $this->resolveSaleItem(
                itemData: $itemData,
                saleItemsById: $saleItemsById,
                saleItemsBySku: $saleItemsBySku,
                saleItemsByIndex: $saleItemsByIndex,
                index: $index,
            );

            if (!$saleItem instanceof ProductSaleItem) {
                continue;
            }

            $discountData = data_get($itemData, 'discount', []);

            if (!is_array($discountData)) {
                $discountData = [];
            }

            $discountedPrice = $this->nullableInt(data_get($discountData, 'discounted_price'));

            if ($discountedPrice === null) {
                $saleItem->discount?->delete();

                continue;
            }

            $basePrice = (int)$saleItem->base_price;

            if ($basePrice > 0 && $discountedPrice >= $basePrice) {
                $saleItem->discount?->delete();

                continue;
            }

            ProductSaleItemDiscount::query()->updateOrCreate(
                [
                    'product_sale_item_id' => $saleItem->id,
                ],
                [
                    'discounted_price' => $discountedPrice,
                    'starts_at' => data_get($discountData, 'starts_at'),
                    'ends_at' => data_get($discountData, 'ends_at'),
                    'show_special_box' => $this->booleanValue(
                        data_get($discountData, 'show_special_box')
                    ),
                    'special_box_title' => $this->nullableString(
                        data_get($discountData, 'special_box_title')
                    ),
                    'special_box_description' => $this->nullableString(
                        data_get($discountData, 'special_box_description')
                    ),
                ]
            );
        }
    }

    /**
     * @param Collection<int, ProductSaleItem> $saleItemsByIndex
     * @param Collection<int, ProductSaleItem> $saleItemsById
     * @param Collection<string, ProductSaleItem> $saleItemsBySku
     */
    private function resolveSaleItem(
        array      $itemData,
        Collection $saleItemsById,
        Collection $saleItemsBySku,
        Collection $saleItemsByIndex,
        int        $index,
    ): ?ProductSaleItem
    {
        $id = $this->nullableInt(data_get($itemData, 'id'));

        if ($id !== null && $saleItemsById->has($id)) {
            return $saleItemsById->get($id);
        }

        $sku = $this->nullableString(data_get($itemData, 'sku'));

        if ($sku !== null && $saleItemsBySku->has($sku)) {
            return $saleItemsBySku->get($sku);
        }

        return $saleItemsByIndex->get($index);
    }

    private function nullableInt(mixed $value): ?int
    {
        if (!filled($value)) {
            return null;
        }

        return max(0, (int)$value);
    }

    private function nullableString(mixed $value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        return trim((string)$value);
    }

    private function booleanValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
