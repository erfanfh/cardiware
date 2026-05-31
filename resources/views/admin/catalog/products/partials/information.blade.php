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

    $assignedAttributeItems = old('attributes', $assignedAttributeItems ?? []);

    $customAttributeItems = old('custom_attributes', $customAttributeItems ?? []);

    $saleItems = old('sale_items', $saleItems ?? []);

    if (empty($saleItems) && !empty($simpleSaleItem)) {
        $saleItems = [
            $simpleSaleItem,
        ];
    }
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
