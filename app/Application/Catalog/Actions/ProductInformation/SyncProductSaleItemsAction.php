<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Application\Catalog\Support\ProductInformation\ProductInformationPayloadNormalizer;
use App\Application\Launcher\Actions\SyncLauncherSaleItemContentAction;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemAttributeValue;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use Illuminate\Support\Str;

class SyncProductSaleItemsAction
{
    public function __construct(
        private readonly ProductInformationPayloadNormalizer $normalizer,
        private readonly SyncProductSaleItemManifestAction   $syncManifestAction,
        private readonly SyncLauncherSaleItemContentAction   $syncLauncherContentAction,
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
        $existingSaleItems = $product->saleItems()
            ->with([
                'files',
                'attributeValues',
                'manifest',
            ])
            ->get();

        $keptSaleItemIds = [];

        foreach (array_values($saleItems) as $index => $item) {
            if (!$this->isMeaningfulSaleItem($product, $item, $index)) {
                continue;
            }

            $saleItem = $this->resolveSaleItem($existingSaleItems, $item);
            $isNewSaleItem = !$saleItem->exists;

            $saleItem->fill([
                'product_id' => $product->id,
                'sku' => $this->normalizer->nullableString($item['sku'] ?? null),
                'title' => $this->resolveSaleItemTitle(
                    product: $product,
                    item: $item,
                    index: $index,
                ),
                'edition_key' => $this->resolveEditionKey(
                    product: $product,
                    item: $item,
                    index: $index,
                ),
                'version' => $this->resolveVersion($item['version'] ?? null),
                'launcher_enabled' => (bool)($item['launcher_enabled'] ?? false),
                'launcher_title' => $this->normalizer->nullableString($item['launcher_title'] ?? null),
                'launcher_excerpt' => $this->normalizer->nullableString($item['launcher_excerpt'] ?? null),
                'launcher_sort_order' => $this->normalizer->integer($item['launcher_sort_order'] ?? $index),
                'base_price' => $this->normalizer->integer($item['base_price'] ?? 0),
                'status' => ProductSaleItemStatusEnum::tryFrom(
                        (string)($item['status'] ?? '')
                    )?->value ?? ProductSaleItemStatusEnum::AVAILABLE->value,
                'download_limit' => $this->normalizer->nullableInteger($item['download_limit'] ?? null),
                'download_expires_in_days' => $this->normalizer->nullableInteger($item['download_expires_in_days'] ?? null),
                'max_quantity_per_order' => $this->normalizer->nullableInteger($item['max_quantity_per_order'] ?? null),
                'description' => $this->normalizer->nullableString($item['description'] ?? null),
                'sort_order' => $this->normalizer->integer($item['sort_order'] ?? $index),
            ]);

            $saleItem->save();

            $keptSaleItemIds[] = $saleItem->id;

            $this->syncFiles($saleItem, is_array($item['files'] ?? null) ? $item['files'] : []);

            if ($isNewSaleItem || array_key_exists('manifest', $item)) {
                $this->syncManifestAction->execute(
                    saleItem: $saleItem,
                    data: is_array($item['manifest'] ?? null)
                        ? $item['manifest']
                        : [],
                );
            }

            $this->syncLauncherContentAction->execute($saleItem, $item);

            $this->syncAttributeValues(
                $saleItem,
                is_array($item['attribute_values'] ?? null)
                    ? $item['attribute_values']
                    : [],
                $customAttributeValueKeyMap
            );
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
            || !empty($item['title'])
            || !empty($item['edition_key'])
            || !empty($item['version'])
            || !empty($item['base_price'])
            || !empty($item['description'])
            || !empty($item['attribute_values'])
            || !empty($item['files'])
            || !empty($item['manifest']);
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
                'file_name' => $this->normalizer->nullableString($fileItem['file_name'] ?? null),
                'url' => $url,
                'size_bytes' => $this->normalizer->nullableInteger($fileItem['size_bytes'] ?? null),
                'sha256' => filled($fileItem['sha256'] ?? null)
                    ? strtolower(trim((string)$fileItem['sha256'])) : null,
                'mime_type' => $this->normalizer->nullableString($fileItem['mime_type'] ?? null),
                'part_number' => $this->normalizer->nullableInteger($fileItem['part_number'] ?? null),
                'archive_group' => $this->normalizer->nullableString($fileItem['archive_group'] ?? null),
                'is_required' => (bool)($fileItem['is_required'] ?? true),
                'is_active' => (bool)($fileItem['is_active'] ?? true),
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

    /**
     * @param array<string, mixed> $item
     */
    private function resolveSaleItemTitle(
        Product $product,
        array   $item,
        int     $index,
    ): string
    {
        $title = $this->normalizer->nullableString(
            $item['title'] ?? null
        );

        if ($title !== null) {
            return $title;
        }

        if (
            $this->productStructureValue($product)
            === ProductStructureEnum::SIMPLE->value
        ) {
            return 'Standard';
        }

        $sku = $this->normalizer->nullableString(
            $item['sku'] ?? null
        );

        return $sku ?? 'Edition ' . ($index + 1);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function resolveEditionKey(
        Product $product,
        array   $item,
        int     $index,
    ): string
    {
        $submittedKey = $this->normalizer->nullableString(
            $item['edition_key'] ?? null
        );

        if ($submittedKey !== null) {
            $normalizedKey = Str::slug(
                $submittedKey,
                '_'
            );

            if ($normalizedKey !== '') {
                return mb_substr($normalizedKey, 0, 80);
            }
        }

        if (
            $this->productStructureValue($product)
            === ProductStructureEnum::SIMPLE->value
        ) {
            return 'standard';
        }

        $source = $this->normalizer->nullableString(
            $item['title'] ?? null
        ) ?? $this->normalizer->nullableString(
            $item['sku'] ?? null
        );

        $generatedKey = $source !== null
            ? Str::slug($source, '_')
            : '';

        return $generatedKey !== ''
            ? mb_substr($generatedKey, 0, 80)
            : 'edition_' . ($index + 1);
    }

    private function resolveVersion(mixed $value): string
    {
        $version = trim((string)$value);

        if (
            preg_match(
                '/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/',
                $version
            )
        ) {
            return mb_substr($version, 0, 50);
        }

        return '1.0.0';
    }
}
