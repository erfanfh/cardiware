<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Application\Catalog\Support\ProductInformation\ProductInformationPayloadNormalizer;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAttributeAssignment;
use App\Domain\Catalog\Models\ProductAttributeAssignmentValue;

class SyncProductGlobalAttributesAction
{
    public function __construct(
        private readonly ProductInformationPayloadNormalizer $normalizer,
    )
    {
    }

    /**
     * Sync global product attributes.
     *
     * @param Product $product
     * @param array $items
     * @return void
     */
    public function execute(Product $product, array $items): void
    {
        $existingAssignments = $product
            ->attributeAssignments()
            ->with('assignmentValues')
            ->get();

        $keptAssignmentIds = [];

        foreach (array_values($items) as $index => $item) {
            $attributeId = $this->normalizer->nullableInteger($item['attribute_id'] ?? null);

            if ($attributeId === null) {
                continue;
            }

            $assignment = $this->resolveAssignment(
                product: $product,
                existingAssignments: $existingAssignments,
                item: $item,
                attributeId: $attributeId
            );

            $assignment->fill([
                'product_id' => $product->id,
                'product_attribute_id' => $attributeId,
                'is_visible' => $this->normalizer->boolean($item['is_visible'] ?? false),
                'is_variation_defining' => $this->normalizer->boolean($item['is_variation_defining'] ?? false),
                'sort_order' => $this->normalizer->integer($item['sort_order'] ?? $index),
            ]);

            $assignment->save();

            $keptAssignmentIds[] = $assignment->id;

            $this->syncAssignmentValues(
                assignment: $assignment,
                valueIds: $item['value_ids'] ?? []
            );
        }

        $product
            ->attributeAssignments()
            ->when(!empty($keptAssignmentIds), fn($query) => $query->whereNotIn('id', $keptAssignmentIds))
            ->get()->each->delete();
    }

    /**
     * Resolve an existing assignment or create a new instance.
     *
     * @param Product $product
     * @param mixed $existingAssignments
     * @param array $item
     * @param int $attributeId
     * @return ProductAttributeAssignment
     */
    private function resolveAssignment(
        Product $product,
        mixed   $existingAssignments,
        array   $item,
        int     $attributeId
    ): ProductAttributeAssignment
    {
        $assignmentId = $this->normalizer->nullableInteger($item['id'] ?? null);

        if ($assignmentId !== null) {
            $assignment = $existingAssignments->firstWhere('id', $assignmentId);

            if ($assignment instanceof ProductAttributeAssignment) {
                return $assignment;
            }
        }

        $assignment = $existingAssignments->firstWhere('product_attribute_id', $attributeId);

        if ($assignment instanceof ProductAttributeAssignment) {
            return $assignment;
        }

        return new ProductAttributeAssignment([
            'product_id' => $product->id,
            'product_attribute_id' => $attributeId,
        ]);
    }

    /**
     * Sync selected values of an assignment.
     *
     * @param ProductAttributeAssignment $assignment
     * @param array $valueIds
     * @return void
     */
    private function syncAssignmentValues(ProductAttributeAssignment $assignment, array $valueIds): void
    {
        $valueIds = collect($valueIds)
            ->map(fn(mixed $value) => $this->normalizer->nullableInteger($value))
            ->filter()
            ->unique()
            ->values();

        $keptIds = [];

        foreach ($valueIds as $index => $valueId) {
            $assignmentValue = ProductAttributeAssignmentValue::query()
                ->firstOrNew([
                    'product_attribute_assignment_id' => $assignment->id,
                    'product_attribute_value_id' => $valueId,
                ]);

            $assignmentValue->fill([
                'sort_order' => $index,
            ]);

            $assignmentValue->save();

            $keptIds[] = $assignmentValue->id;
        }

        $assignment
            ->assignmentValues()
            ->when(!empty($keptIds), fn($query) => $query->whereNotIn('id', $keptIds))
            ->get()->each->delete();
    }
}
