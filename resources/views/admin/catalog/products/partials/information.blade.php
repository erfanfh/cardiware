@php
    use App\Domain\Catalog\Enums\ProductContentTypeEnum;
    use App\Domain\Catalog\Enums\ProductStructureEnum;
    use App\Domain\Catalog\Enums\ProductTypeEnum;
    use App\Domain\Catalog\Models\ProductAttribute;

    $digitalType = ProductTypeEnum::DIGITAL->value;
    $physicalType = ProductTypeEnum::PHYSICAL->value;

    $simpleStructure = ProductStructureEnum::SIMPLE->value;
    $variableStructure = ProductStructureEnum::VARIABLE->value;

    $productContentTypes = collect(ProductContentTypeEnum::cases())
        ->mapWithKeys(fn (ProductContentTypeEnum $type) => [
            $type->value => $type->label(),
        ])
        ->toArray();

    $currentType = old('type', isset($product)
        ? ($product->type instanceof \BackedEnum ? $product->type->value : $product->type)
        : $digitalType
    );

    $currentContentType = old('content_type', isset($product)
        ? ($product->content_type instanceof \BackedEnum ? $product->content_type->value : $product->content_type)
        : ''
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

    $assignedAttributeItems = !isset($product) ? [] : $product
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

    $customAttributeItems = !isset($product) ? [] : $product
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

    $manifestFormDefaults = [
        /*
         * Empty means automatic detection by the backend.
         */
        'download_type' => '',

        'archive_format' => '',
        'assembled_file_name' => '',

        'install_directory' => '',
        'requires_administrator' => false,

        'main_executable_path' => '',
        'working_directory' => '.',
        'launch_arguments' => '',

        'requires_sider' => false,
        'sider_executable_path' => '',
        'sider_working_directory' => '',
        'sider_arguments' => '',
        'run_sider_as_administrator' => false,

        'capabilities' => [
            'supports_install' => true,
            'supports_launch' => true,
            'supports_settings' => false,
            'supports_profiles' => false,
        ],

        'install_options' => [
            'extract_enabled' => true,
            'delete_archives_after_install' => true,
            'ask_before_delete_archives' => true,
            'clean_temp_after_install' => true,
            'create_desktop_shortcut' => false,
        ],
    ];

    $makeSaleItemManifestFormData = static function ($saleItem) use (
        $manifestFormDefaults
    ): array {
        $manifest = $saleItem->manifest;

        if (!$manifest) {
            return $manifestFormDefaults;
        }

        $downloadType = $manifest->download_type instanceof \BackedEnum
            ? $manifest->download_type->value
            : $manifest->download_type;

        return array_replace_recursive(
            $manifestFormDefaults,
            [
                'download_type' => $downloadType ?? '',

                'archive_format' => $manifest->archive_format ?? '',

                'assembled_file_name' => $manifest->assembled_file_name ?? '',

                'install_directory' => $manifest->install_directory ?? '',

                'requires_administrator' => (bool) $manifest->requires_administrator,

                'main_executable_path' => $manifest->main_executable_path ?? '',

                'working_directory' => $manifest->working_directory ?? '.',

                'launch_arguments' => $manifest->launch_arguments ?? '',

                'requires_sider' => (bool) $manifest->requires_sider,

                'sider_executable_path' => $manifest->sider_executable_path ?? '',

                'sider_working_directory' => $manifest->sider_working_directory ?? '',

                'sider_arguments' => $manifest->sider_arguments ?? '',

                'run_sider_as_administrator' => (bool) $manifest->run_sider_as_administrator,

                'capabilities' => $manifest->resolvedCapabilities(),

                'install_options' => $manifest->resolvedInstallOptions(),
            ]
        );
    };

    $saleItems = !isset($product) ? [] : $product
        ->saleItems()
        ->with([
            'discount',
            'manifest',
            'files' => fn ($query) => $query->orderBy('sort_order'),

            'attributeValues.globalValue.attribute',
            'attributeValues.customValue.customAttribute',
        ])
        ->orderBy('sort_order')
        ->get()
        ->map(function ($saleItem) use ($makeSaleItemManifestFormData) {
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
                'title' => $saleItem->title,
                'edition_key' => $saleItem->edition_key,
                'version' => $saleItem->version,
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
                'discount' => [
                    'id' => $saleItem->discount?->id,
                    'discounted_price' => $saleItem->discount?->discounted_price,
                    'starts_at' => $saleItem->discount?->starts_at
                        ? jalali_date($saleItem->discount->starts_at)
                        : null,
                    'ends_at' => $saleItem->discount?->ends_at
                        ? jalali_date($saleItem->discount->ends_at)
                        : null,
                    'show_special_box' => (bool) $saleItem->discount?->show_special_box,
                    'special_box_title' => $saleItem->discount?->special_box_title,
                    'special_box_description' => $saleItem->discount?->special_box_description,
                ],
                'files' => $saleItem->files
                    ->map(fn ($file) => [
                        'id' => $file->id,
                        'title' => $file->title,
                        'url' => $file->url,
                        'sort_order' => (int) $file->sort_order,
                    ])
                    ->values()
                    ->all(),
                'manifest' => $makeSaleItemManifestFormData(
                    $saleItem
                ),
            ];
        })
        ->values()
        ->all();

    $saleItems = old('sale_items', $saleItems ?? []);

    $simpleSaleItem = $saleItems[0] ?? $simpleSaleItem ?? [];
    $simpleSaleItemFiles = old('sale_items.0.files', $simpleSaleItem['files'] ?? []);
    $simpleSaleItemDiscount = old('sale_items.0.discount', $simpleSaleItem['discount'] ?? [
        'id' => null,
        'discounted_price' => null,
        'starts_at' => null,
        'ends_at' => null,
        'show_special_box' => false,
        'special_box_title' => null,
        'special_box_description' => null,
    ]);

    $productAddons = !isset($product) ? [] : $product
        ->addons()
        ->orderBy('sort_order')
        ->get()
        ->map(fn ($addon) => [
            'id' => $addon->id,
            'title' => $addon->title,
            'description' => $addon->description,
            'price' => $addon->price,
            'is_active' => (string) (bool) $addon->is_active,
            'sort_order' => (int) $addon->sort_order,
        ])
        ->values()
        ->all();

    $productAddons = old('addons', $productAddons ?? []);
@endphp

<div
    x-data="productInformationBox({
        defaultTab: 'basic',
        values: {
            type: @js($currentType),
            structure: @js($currentStructure),
            content_type: @js($currentContentType),
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
            content_types: {
                game: @js(ProductContentTypeEnum::GAME->value),
                patch: @js(ProductContentTypeEnum::PATCH->value),
            },
        },
        attributes: {
            available: @js($availableAttributes),
            assigned: @js($assignedAttributeItems),
            custom: @js($customAttributeItems),
        },
        saleItems: @js($saleItems),
        simpleFiles: @js($simpleSaleItemFiles),
        addons: @js($productAddons),
    })"
    class="space-y-4"
>
    @include('admin.catalog.products.partials.information.tabs')

    <div>
        @include('admin.catalog.products.partials.information.basic')

        @include('admin.catalog.products.partials.information.attributes')

        @include('admin.catalog.products.partials.information.pricing')

        @include('admin.catalog.products.partials.information.addons')
    </div>
</div>

@include('admin.catalog.products.partials.information.scripts')
