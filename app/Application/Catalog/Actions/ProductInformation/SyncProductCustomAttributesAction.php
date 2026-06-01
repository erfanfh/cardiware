<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Application\Catalog\Support\ProductInformation\ProductInformationPayloadNormalizer;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductCustomAttribute;
use App\Domain\Catalog\Models\ProductCustomAttributeValue;

class SyncProductCustomAttributesAction
{
    public function __construct(
        private readonly ProductInformationPayloadNormalizer $normalizer,
    )
    {
    }

    /**
     * Sync custom product attributes.
     *
     * Returns a frontend custom value key to database id map.
     *
     * @param Product $product
     * @param array $items
     * @return array<string, int>
     */
    public function execute(Product $product, array $items): array
    {
        $existingAttributes = $product
            ->customAttributes()
            ->with('values')
            ->get();

        $keptAttributeIds = [];
        $customValueKeyMap = [];

        foreach (array_values($items) as $index => $item) {
            $title = trim((string)($item['title'] ?? ''));

            if ($title === '' && empty($item['values'])) {
                continue;
            }

            $customAttribute = $this->resolveCustomAttribute(
                product: $product,
                existingAttributes: $existingAttributes,
                item: $item
            );

            $customAttribute->fill([
                'product_id' => $product->id,
                'title' => $title,
                'is_visible' => $this->normalizer->boolean($item['is_visible'] ?? false),
                'is_variation_defining' => $this->normalizer->boolean($item['is_variation_defining'] ?? false),
                'sort_order' => $this->normalizer->integer($item['sort_order'] ?? $index),
            ]);

            $customAttribute->save();

            $keptAttributeIds[] = $customAttribute->id;

            $customValueKeyMap = array_replace(
                $customValueKeyMap,
                $this->syncCustomAttributeValues(
                    customAttribute: $customAttribute,
                    values: $item['values'] ?? [],
                    attributeIndex: $index,
                    submittedAttributeId: $this->normalizer->nullableInteger($item['id'] ?? null)
                )
            );
        }

        $product
            ->customAttributes()
            ->when(!empty($keptAttributeIds), fn($query) => $query->whereNotIn('id', $keptAttributeIds))
            ->delete();

        return $customValueKeyMap;
    }

    /**
     * Resolve an existing custom attribute or create a new instance.
     *
     * @param Product $product
     * @param mixed $existingAttributes
     * @param array $item
     * @return ProductCustomAttribute
     */
    private function resolveCustomAttribute(
        Product $product,
        mixed   $existingAttributes,
        array   $item
    ): ProductCustomAttribute
    {
        $customAttributeId = $this->normalizer->nullableInteger($item['id'] ?? null);

        if ($customAttributeId !== null) {
            $customAttribute = $existingAttributes->firstWhere('id', $customAttributeId);

            if ($customAttribute instanceof ProductCustomAttribute) {
                return $customAttribute;
            }
        }

        return new ProductCustomAttribute([
            'product_id' => $product->id,
        ]);
    }

    /**
     * Sync custom attribute values.
     *
     * @param ProductCustomAttribute $customAttribute
     * @param array $values
     * @param int $attributeIndex
     * @param int|null $submittedAttributeId
     * @return array<string, int>
     */
    private function syncCustomAttributeValues(
        ProductCustomAttribute $customAttribute,
        array                  $values,
        int                    $attributeIndex,
        ?int                   $submittedAttributeId
    ): array
    {
        $existingValues = $customAttribute->values()->get();

        $keptValueIds = [];
        $valueKeyMap = [];

        foreach (array_values($values) as $valueIndex => $valueItem) {
            $title = trim((string)($valueItem['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $customValue = $this->resolveCustomAttributeValue(
                existingValues: $existingValues,
                valueItem: $valueItem
            );

            $customValue->fill([
                'product_custom_attribute_id' => $customAttribute->id,
                'title' => $title,
                'sort_order' => $this->normalizer->integer($valueItem['sort_order'] ?? $valueIndex),
            ]);

            $customValue->save();

            $keptValueIds[] = $customValue->id;

            foreach ($this->frontendAttributeKeys($customAttribute->id, $attributeIndex, $submittedAttributeId) as $attributeKey) {
                $valueKeyMap["{$attributeKey}_{$customValue->id}"] = $customValue->id;
                $valueKeyMap["{$attributeKey}_new_{$valueIndex}"] = $customValue->id;
            }

            $submittedValueId = $this->normalizer->nullableInteger($valueItem['id'] ?? null);

            if ($submittedValueId !== null) {
                foreach ($this->frontendAttributeKeys($customAttribute->id, $attributeIndex, $submittedAttributeId) as $attributeKey) {
                    $valueKeyMap["{$attributeKey}_{$submittedValueId}"] = $customValue->id;
                }
            }
        }

        $customAttribute
            ->values()
            ->when(!empty($keptValueIds), fn($query) => $query->whereNotIn('id', $keptValueIds))
            ->delete();

        return $valueKeyMap;
    }

    /**
     * Resolve an existing custom value or create a new instance.
     *
     * @param mixed $existingValues
     * @param array $valueItem
     * @return ProductCustomAttributeValue
     */
    private function resolveCustomAttributeValue(
        mixed $existingValues,
        array $valueItem
    ): ProductCustomAttributeValue
    {
        $valueId = $this->normalizer->nullableInteger($valueItem['id'] ?? null);

        if ($valueId !== null) {
            $customValue = $existingValues->firstWhere('id', $valueId);

            if ($customValue instanceof ProductCustomAttributeValue) {
                return $customValue;
            }
        }

        return new ProductCustomAttributeValue();
    }

    /**
     * Get possible frontend attribute keys for custom attributes.
     *
     * @param int $customAttributeId
     * @param int $attributeIndex
     * @param int|null $submittedAttributeId
     * @return array<int, string>
     */
    private function frontendAttributeKeys(
        int  $customAttributeId,
        int  $attributeIndex,
        ?int $submittedAttributeId
    ): array
    {
        $keys = [
            "custom_{$customAttributeId}",
            "custom_new_{$attributeIndex}",
        ];

        if ($submittedAttributeId !== null) {
            $keys[] = "custom_{$submittedAttributeId}";
        }

        return array_values(array_unique($keys));
    }
}
