<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Application\Catalog\Support\ProductInformation\ProductInformationPayloadNormalizer;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemAttributeValue;
use App\Domain\Catalog\Models\ProductSaleItemFile;

class SyncProductSaleItemsAction
{
    public function __construct(
        private readonly ProductInformationPayloadNormalizer $normalizer,
    )
    {
    }

    /**
     * Sync product sale items, files, and variation values.
     *
     * @param Product $product
     * @param array $payload
     * @param array<string, int> $customAttributeValueKeyMap
     * @return void
     */
    public function execute(Product $product, array $payload, array $customAttributeValueKeyMap): void
    {
        if ($this->productTypeValue($product) === ProductTypeEnum::PHYSICAL->value) {
            $product->saleItems()->delete();

            return;
        }

        $saleItems = $this->resolveSubmittedSaleItems($product, $payload);
        $existingSaleItems = $product->saleItems()->with(['files', 'attributeValues'])->get();

        $keptSaleItemIds = [];

        foreach (array_values($saleItems) as $index => $item) {
            if (!$this->isMeaningfulSaleItem($product, $item, $index)) {
                continue;
            }

            $saleItem = $this->resolveSaleItem($existingSaleItems, $item);

            $saleItem->fill([
                'product_id' => $product->id,
                'sku' => $this->normalizer->nullableString($item['sku'] ?? null),
                'base_price' => $this->normalizer->integer($item['base_price'] ?? 0),
                'status' => $item['status'] ?? ProductSaleItemStatusEnum::AVAILABLE->value,
                'download_limit' => $this->normalizer->nullableInteger($item['download_limit'] ?? null),
                'download_expires_in_days' => $this->normalizer->nullableInteger($item['download_expires_in_days'] ?? null),
                'max_quantity_per_order' => $this->normalizer->nullableInteger($item['max_quantity_per_order'] ?? null),
                'description' => $this->normalizer->nullableString($item['description'] ?? null),
                'sort_order' => $this->normalizer->integer($item['sort_order'] ?? $index),
            ]);

            $saleItem->save();

            $keptSaleItemIds[] = $saleItem->id;

            $this->syncFiles($saleItem, $item['files'] ?? []);
            $this->syncAttributeValues($saleItem, $item['attribute_values'] ?? [], $customAttributeValueKeyMap);
        }

        $product
            ->saleItems()
            ->when(!empty($keptSaleItemIds), fn($query) => $query->whereNotIn('id', $keptSaleItemIds))
            ->delete();
    }

    /**
     * Resolve submitted sale items based on product structure.
     *
     * @param Product $product
     * @param array $payload
     * @return array
     */
    private function resolveSubmittedSaleItems(Product $product, array $payload): array
    {
        $saleItems = $payload['sale_items'] ?? [];

        if ($this->productStructureValue($product) === ProductStructureEnum::SIMPLE->value) {
            return [
                $saleItems[0] ?? [],
            ];
        }

        return $saleItems;
    }

    /**
     * Check if a sale item should be saved.
     *
     * @param Product $product
     * @param array $item
     * @param int $index
     * @return bool
     */
    private function isMeaningfulSaleItem(Product $product, array $item, int $index): bool
    {
        if ($this->productStructureValue($product) === ProductStructureEnum::SIMPLE->value && $index === 0) {
            return true;
        }

        return !empty($item['id'])
            || !empty($item['sku'])
            || !empty($item['base_price'])
            || !empty($item['description'])
            || !empty($item['attribute_values'])
            || !empty($item['files']);
    }

    /**
     * Resolve existing sale item or create a new one.
     *
     * @param mixed $existingSaleItems
     * @param array $item
     * @return ProductSaleItem
     */
    private function resolveSaleItem(mixed $existingSaleItems, array $item): ProductSaleItem
    {
        $saleItemId = $this->normalizer->nullableInteger($item['id'] ?? null);

        if ($saleItemId !== null) {
            $saleItem = $existingSaleItems->firstWhere('id', $saleItemId);

            if ($saleItem instanceof ProductSaleItem) {
                return $saleItem;
            }
        }

        return new ProductSaleItem();
    }

    /**
     * Sync downloadable files.
     *
     * @param ProductSaleItem $saleItem
     * @param array $files
     * @return void
     */
    private function syncFiles(ProductSaleItem $saleItem, array $files): void
    {
        $existingFiles = $saleItem->files()->get();
        $keptFileIds = [];

        foreach (array_values($files) as $index => $fileItem) {
            $url = trim((string)($fileItem['url'] ?? ''));

            if ($url === '') {
                continue;
            }

            $file = $this->resolveFile($existingFiles, $fileItem);

            $file->fill([
                'product_sale_item_id' => $saleItem->id,
                'title' => $this->normalizer->nullableString($fileItem['title'] ?? null),
                'url' => $url,
                'sort_order' => $this->normalizer->integer($fileItem['sort_order'] ?? $index),
            ]);

            $file->save();

            $keptFileIds[] = $file->id;
        }

        $saleItem
            ->files()
            ->when(!empty($keptFileIds), fn($query) => $query->whereNotIn('id', $keptFileIds))
            ->delete();
    }

    /**
     * Resolve existing file or create a new one.
     *
     * @param mixed $existingFiles
     * @param array $fileItem
     * @return ProductSaleItemFile
     */
    private function resolveFile(mixed $existingFiles, array $fileItem): ProductSaleItemFile
    {
        $fileId = $this->normalizer->nullableInteger($fileItem['id'] ?? null);

        if ($fileId !== null) {
            $file = $existingFiles->firstWhere('id', $fileId);

            if ($file instanceof ProductSaleItemFile) {
                return $file;
            }
        }

        return new ProductSaleItemFile();
    }

    /**
     * Sync selected attribute values for a sale item.
     *
     * @param ProductSaleItem $saleItem
     * @param array $attributeValues
     * @param array<string, int> $customAttributeValueKeyMap
     * @return void
     */
    private function syncAttributeValues(
        ProductSaleItem $saleItem,
        array           $attributeValues,
        array           $customAttributeValueKeyMap
    ): void
    {
        $saleItem->attributeValues()->delete();

        foreach ($attributeValues as $frontendValueKey) {
            $frontendValueKey = trim((string)$frontendValueKey);

            if ($frontendValueKey === '') {
                continue;
            }

            if (str_starts_with($frontendValueKey, 'global_')) {
                $globalValueId = $this->extractGlobalValueId($frontendValueKey);

                if ($globalValueId !== null) {
                    ProductSaleItemAttributeValue::query()->create([
                        'product_sale_item_id' => $saleItem->id,
                        'product_attribute_value_id' => $globalValueId,
                        'product_custom_attribute_value_id' => null,
                    ]);
                }

                continue;
            }

            if (str_starts_with($frontendValueKey, 'custom_')) {
                $customValueId = $customAttributeValueKeyMap[$frontendValueKey] ?? null;

                if ($customValueId !== null) {
                    ProductSaleItemAttributeValue::query()->create([
                        'product_sale_item_id' => $saleItem->id,
                        'product_attribute_value_id' => null,
                        'product_custom_attribute_value_id' => $customValueId,
                    ]);
                }
            }
        }
    }

    /**
     * Extract global attribute value id from frontend value key.
     *
     * Example: global_12_45 => 45
     *
     * @param string $frontendValueKey
     * @return int|null
     */
    private function extractGlobalValueId(string $frontendValueKey): ?int
    {
        if (!preg_match('/^global_(\d+)_(\d+)$/', $frontendValueKey, $matches)) {
            return null;
        }

        return $this->normalizer->nullableInteger($matches[2] ?? null);
    }

    /**
     * Get product type value.
     *
     * @param Product $product
     * @return string|null
     */
    private function productTypeValue(Product $product): ?string
    {
        return $product->type instanceof \BackedEnum
            ? $product->type->value
            : $product->type;
    }

    /**
     * Get product structure value.
     *
     * @param Product $product
     * @return string|null
     */
    private function productStructureValue(Product $product): ?string
    {
        return $product->structure instanceof \BackedEnum
            ? $product->structure->value
            : $product->structure;
    }
}
