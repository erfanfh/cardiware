@php
    use App\Domain\Catalog\Enums\ProductStructureEnum;
    use App\Domain\Catalog\Enums\ProductTypeEnum;
    use App\Domain\Catalog\Models\ProductAttribute;

    $digitalType = ProductTypeEnum::DIGITAL->value;
    $physicalType = ProductTypeEnum::PHYSICAL->value;

    $simpleStructure = ProductStructureEnum::SIMPLE->value;
    $variableStructure = ProductStructureEnum::VARIABLE->value;

    $currentType = old('type', isset($product)
        ? ($product->type instanceof \BackedEnum ? $product->type->value : $product->type)
        : $digitalType
    );

    $currentStructure = old('structure', isset($product)
        ? ($product->structure instanceof \BackedEnum ? $product->structure->value : $product->structure)
        : $simpleStructure
    );

    $currentSku = old('sku', $product->sku ?? null);

    $simpleSaleItem = $simpleSaleItem ?? [];

    $simpleSaleItemFiles = old('sale_items.0.files', $simpleSaleItem['files'] ?? []);

    $availableAttributes = ProductAttribute::query()
        ->with(['values' => function ($query) {
            $query->orderBy('title');
        }])
        ->orderBy('title')
        ->get()
        ->map(fn ($attribute) => [
            'id' => $attribute->id,
            'title' => $attribute->title,
            'values' => $attribute->values
                ->map(fn ($value) => [
                    'id' => $value->id,
                    'title' => $value->title,
                ])
                ->values()
                ->all(),
        ])
        ->values()
        ->all();

    $assignedAttributeItems = $product
        ->attributeAssignments()
        ->with([
            'attribute.values' => fn ($query) => $query->orderBy('title'),
            'assignmentValues.value',
        ])
        ->orderBy('sort_order')
        ->get()
        ->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'attribute_id' => $assignment->product_attribute_id,
                'title' => $assignment->attribute?->title,
                'values' => $assignment->attribute?->values
                    ? $assignment->attribute->values
                        ->map(fn ($value) => [
                            'id' => $value->id,
                            'title' => $value->title,
                        ])
                        ->values()
                        ->all()
                    : [],
                'selected_value_ids' => $assignment->assignmentValues
                    ->pluck('product_attribute_value_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
                'is_visible' => (bool) $assignment->is_visible,
                'is_variation_defining' => (bool) $assignment->is_variation_defining,
                'sort_order' => (int) $assignment->sort_order,
            ];
        })
        ->values()
        ->all();

    $assignedAttributeItems = old('attributes', $assignedAttributeItems ?? []);

    $customAttributeItems = $product
        ->customAttributes()
        ->with([
            'values' => fn ($query) => $query->orderBy('sort_order'),
        ])
        ->orderBy('sort_order')
        ->get()
        ->map(function ($attribute) {
            return [
                'id' => $attribute->id,
                'title' => $attribute->title,
                'values' => $attribute->values
                    ->map(fn ($value) => [
                        'id' => $value->id,
                        'title' => $value->title,
                        'sort_order' => (int) $value->sort_order,
                    ])
                    ->values()
                    ->all(),
                'is_visible' => (bool) $attribute->is_visible,
                'is_variation_defining' => (bool) $attribute->is_variation_defining,
                'sort_order' => (int) $attribute->sort_order,
            ];
        })
        ->values()
        ->all();

    $customAttributeItems = old('custom_attributes', $customAttributeItems ?? []);

    $saleItems = $product
        ->saleItems()
        ->with([
            'files' => fn ($query) => $query->orderBy('sort_order'),

            'attributeValues.globalValue.attribute',
            'attributeValues.customValue.customAttribute',
        ])
        ->orderBy('sort_order')
        ->get()
        ->map(function ($saleItem) {
            $attributeValues = [];

            foreach ($saleItem->attributeValues as $selectedValue) {
                if ($selectedValue->globalValue) {
                    $attributeId = $selectedValue->globalValue->product_attribute_id;
                    $valueId = $selectedValue->globalValue->id;

                    $attributeValues["global_{$attributeId}"] = "global_{$attributeId}_{$valueId}";
                }

                if ($selectedValue->customValue) {
                    $customAttributeId = $selectedValue->customValue->product_custom_attribute_id;
                    $customValueId = $selectedValue->customValue->id;

                    $attributeValues["custom_{$customAttributeId}"] = "custom_{$customAttributeId}_{$customValueId}";
                }
            }

            return [
                'id' => $saleItem->id,
                'sku' => $saleItem->sku,
                'base_price' => $saleItem->base_price,
                'status' => $saleItem->status instanceof \BackedEnum
                    ? $saleItem->status->value
                    : $saleItem->status,
                'download_limit' => $saleItem->download_limit,
                'download_expires_in_days' => $saleItem->download_expires_in_days,
                'max_quantity_per_order' => $saleItem->max_quantity_per_order,
                'description' => $saleItem->description,
                'sort_order' => (int) $saleItem->sort_order,
                'attribute_values' => $attributeValues,
                'files' => $saleItem->files
                    ->map(fn ($file) => [
                        'id' => $file->id,
                        'title' => $file->title,
                        'url' => $file->url,
                        'sort_order' => (int) $file->sort_order,
                    ])
                    ->values()
                    ->all(),
            ];
        })
        ->values()
        ->all();

    $saleItems = old('sale_items', $saleItems ?? []);

    $simpleSaleItem = $saleItems[0] ?? $simpleSaleItem ?? [];
    $simpleSaleItemFiles = old('sale_items.0.files', $simpleSaleItem['files'] ?? []);
    
@endphp

<div
    x-data="productInformationBox({
        defaultTab: 'basic',
        values: {
            type: @js($currentType),
            structure: @js($currentStructure),
        },
        enums: {
            types: {
                digital: @js($digitalType),
                physical: @js($physicalType),
            },
            structures: {
                simple: @js($simpleStructure),
                variable: @js($variableStructure),
            },
        },
        attributes: {
            available: @js($availableAttributes),
            assigned: @js($assignedAttributeItems),
            custom: @js($customAttributeItems),
        },
        saleItems: @js($saleItems),
        simpleFiles: @js($simpleSaleItemFiles),
    })"
    class="space-y-4"
>
    @include('admin.catalog.products.partials.information.tabs')

    <div>
        @include('admin.catalog.products.partials.information.basic')

        @include('admin.catalog.products.partials.information.attributes')

        @include('admin.catalog.products.partials.information.pricing')
    </div>
</div>

@include('admin.catalog.products.partials.information.scripts')
