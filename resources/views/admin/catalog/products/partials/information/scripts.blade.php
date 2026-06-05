@once
    @push('scripts')
        <script>
            function productInformationBox(config) {
                return {
                    activeTab: config.defaultTab ?? 'basic',

                    productType: config.values?.type ?? config.enums.types.digital,
                    productStructure: config.values?.structure ?? config.enums.structures.simple,

                    typeEnums: config.enums.types,
                    structureEnums: config.enums.structures,

                    availableAttributes: config.attributes?.available ?? [],
                    selectedAttributeId: '',

                    assignedAttributes: [],
                    customAttributes: [],
                    saleItems: [],
                    simpleFiles: [],
                    productAddons: [],

                    init() {
                        this.assignedAttributes = this.normalizeAssignedAttributes(
                            config.attributes?.assigned ?? []
                        );

                        this.customAttributes = this.normalizeCustomAttributes(
                            config.attributes?.custom ?? []
                        );

                        this.saleItems = this.normalizeSaleItems(config.saleItems ?? []);

                        this.simpleFiles = this.normalizeSaleItemFiles(config.simpleFiles ?? []);

                        this.productAddons = this.normalizeProductAddons(config.addons ?? []);
                    },

                    get tabs() {
                        return [
                            {
                                key: 'basic',
                                label: 'اطلاعات اصلی',
                                badge: null,
                            },
                            {
                                key: 'attributes',
                                label: 'ویژگی‌ها',
                                badge: this.attributesCount,
                            },
                            {
                                key: 'pricing',
                                label: 'قیمت‌گذاری و فایل‌ها',
                                badge: null,
                            },
                            {
                                key: 'addons',
                                label: 'خدمات افزوده',
                                badge: this.productAddons.length,
                            },
                        ];
                    },

                    get attributesCount() {
                        return this.assignedAttributes.length + this.customAttributes.length;
                    },

                    get isPhysicalProduct() {
                        return this.productType === this.typeEnums.physical;
                    },

                    get isDigitalProduct() {
                        return this.productType === this.typeEnums.digital;
                    },

                    get isSimpleDigitalProduct() {
                        return this.isDigitalProduct
                            && this.productStructure === this.structureEnums.simple;
                    },

                    get isVariableDigitalProduct() {
                        return this.isDigitalProduct
                            && this.productStructure === this.structureEnums.variable;
                    },

                    get productTypeLabel() {
                        if (this.productType === this.typeEnums.physical) {
                            return 'فیزیکی';
                        }

                        return 'مجازی';
                    },

                    get productStructureLabel() {
                        if (this.productStructure === this.structureEnums.variable) {
                            return 'متغیر';
                        }

                        return 'ساده';
                    },

                    makeKey(prefix = 'item') {
                        return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2)}`;
                    },

                    normalizeBoolean(value, fallback = false) {
                        if (value === true || value === 1 || value === '1') {
                            return true;
                        }

                        if (value === false || value === 0 || value === '0') {
                            return false;
                        }

                        return fallback;
                    },

                    normalizeAssignedAttributes(items) {
                        return (items ?? []).map((item) => {
                            const attributeId = Number(item.attribute_id ?? item.id ?? 0);
                            const sourceAttribute = this.findAvailableAttribute(attributeId);

                            const selectedValueIds = item.selected_value_ids
                                ?? item.value_ids
                                ?? [];

                            return {
                                __key: item.__key ?? this.makeKey('global-attribute'),
                                id: item.id ?? null,
                                attribute_id: attributeId,
                                title: item.title ?? sourceAttribute?.title ?? 'ویژگی',
                                values: item.values ?? sourceAttribute?.values ?? [],
                                selected_value_ids: selectedValueIds.map((id) => Number(id)),
                                is_visible: this.normalizeBoolean(item.is_visible, true),
                                is_variation_defining: this.normalizeBoolean(item.is_variation_defining, false),
                                sort_order: item.sort_order ?? 0,
                                _open: this.normalizeBoolean(item._open, false),
                            };
                        });
                    },

                    normalizeCustomAttributes(items) {
                        return (items ?? []).map((item) => {
                            return {
                                __key: item.__key ?? this.makeKey('custom-attribute'),
                                id: item.id ?? null,
                                title: item.title ?? '',
                                values: this.normalizeCustomAttributeValues(item.values ?? []),
                                is_visible: this.normalizeBoolean(item.is_visible, true),
                                is_variation_defining: this.normalizeBoolean(item.is_variation_defining, false),
                                sort_order: item.sort_order ?? 0,
                                _open: this.normalizeBoolean(item._open, false),
                            };
                        });
                    },

                    normalizeCustomAttributeValues(values) {
                        return (values ?? []).map((value) => {
                            return {
                                __key: value.__key ?? this.makeKey('custom-attribute-value'),
                                id: value.id ?? null,
                                title: value.title ?? '',
                                sort_order: value.sort_order ?? 0,
                            };
                        });
                    },

                    findAvailableAttribute(attributeId) {
                        return this.availableAttributes.find((attribute) => {
                            return Number(attribute.id) === Number(attributeId);
                        });
                    },

                    isGlobalAttributeAssigned(attributeId) {
                        return this.assignedAttributes.some((attribute) => {
                            return Number(attribute.attribute_id) === Number(attributeId);
                        });
                    },

                    addSelectedGlobalAttribute() {
                        const attributeId = Number(this.selectedAttributeId);

                        if (!attributeId || this.isGlobalAttributeAssigned(attributeId)) {
                            return;
                        }

                        const sourceAttribute = this.findAvailableAttribute(attributeId);

                        if (!sourceAttribute) {
                            return;
                        }

                        this.assignedAttributes.push({
                            __key: this.makeKey('global-attribute'),
                            id: null,
                            attribute_id: sourceAttribute.id,
                            title: sourceAttribute.title,
                            values: sourceAttribute.values ?? [],
                            selected_value_ids: [],
                            is_visible: true,
                            is_variation_defining: false,
                            sort_order: this.assignedAttributes.length,
                            _open: true,
                        });

                        this.selectedAttributeId = '';
                    },

                    removeGlobalAttribute(index) {
                        this.assignedAttributes.splice(index, 1);
                    },

                    toggleGlobalAttributeValue(attribute, valueId) {
                        const normalizedValueId = Number(valueId);
                        const exists = attribute.selected_value_ids.includes(normalizedValueId);

                        if (exists) {
                            attribute.selected_value_ids = attribute.selected_value_ids.filter((id) => {
                                return Number(id) !== normalizedValueId;
                            });

                            return;
                        }

                        attribute.selected_value_ids.push(normalizedValueId);
                    },

                    addCustomAttribute() {
                        this.customAttributes.push({
                            __key: this.makeKey('custom-attribute'),
                            id: null,
                            title: '',
                            values: [],
                            is_visible: true,
                            is_variation_defining: false,
                            sort_order: this.customAttributes.length,
                            _open: true,
                        });
                    },

                    removeCustomAttribute(index) {
                        this.customAttributes.splice(index, 1);
                    },

                    addCustomAttributeValue(attribute) {
                        attribute.values.push({
                            __key: this.makeKey('custom-attribute-value'),
                            id: null,
                            title: '',
                            sort_order: attribute.values.length,
                        });
                    },

                    removeCustomAttributeValue(attribute, valueIndex) {
                        attribute.values.splice(valueIndex, 1);
                    },

                    normalizeSaleItems(items) {
                        return (items ?? []).map((item) => {
                            return {
                                __key: item.__key ?? this.makeKey('sale-item'),
                                id: item.id ?? null,
                                sku: item.sku ?? '',
                                base_price: item.base_price ?? '',
                                status: item.status ?? 'available',
                                download_limit: item.download_limit ?? '',
                                download_expires_in_days: item.download_expires_in_days ?? '',
                                max_quantity_per_order: item.max_quantity_per_order ?? '',
                                description: item.description ?? '',
                                attribute_values: this.normalizeSaleItemAttributeValues(item.attribute_values ?? {}),
                                files: this.normalizeSaleItemFiles(item.files ?? []),
                                _open: this.normalizeBoolean(item._open, false),
                            };
                        });
                    },

                    normalizeSaleItemAttributeValues(attributeValues) {
                        if (Array.isArray(attributeValues)) {
                            return {};
                        }

                        return Object.entries(attributeValues ?? {}).reduce((carry, [key, value]) => {
                            carry[String(key)] = String(value);
                            return carry;
                        }, {});
                    },

                    normalizeSaleItemFiles(files) {
                        return (files ?? []).map((file) => {
                            return {
                                __key: file.__key ?? this.makeKey('sale-item-file'),
                                id: file.id ?? null,
                                title: file.title ?? '',
                                url: file.url ?? '',
                                sort_order: file.sort_order ?? 0,
                            };
                        });
                    },

                    get saleItemVariationAttributes() {
                        const globalAttributes = this.assignedAttributes
                            .filter((attribute) => {
                                return attribute.is_variation_defining && attribute.selected_value_ids.length > 0;
                            })
                            .map((attribute) => {
                                const selectedValues = attribute.values.filter((value) => {
                                    return attribute.selected_value_ids.includes(Number(value.id));
                                });

                                return {
                                    key: `global_${attribute.attribute_id}`,
                                    type: 'global',
                                    id: attribute.attribute_id,
                                    title: attribute.title,
                                    values: selectedValues.map((value) => {
                                        return {
                                            key: `global_${attribute.attribute_id}_${value.id}`,
                                            id: value.id,
                                            title: value.title,
                                        };
                                    }),
                                };
                            })
                            .filter((attribute) => attribute.values.length > 0);

                        const customAttributes = this.customAttributes
                            .filter((attribute) => {
                                return attribute.is_variation_defining && attribute.values.length > 0;
                            })
                            .map((attribute, index) => {
                                const attributeKey = attribute.id
                                    ? `custom_${attribute.id}`
                                    : `custom_new_${index}`;

                                return {
                                    key: attributeKey,
                                    type: 'custom',
                                    id: attribute.id,
                                    title: attribute.title || 'ویژگی اختصاصی',
                                    values: attribute.values
                                        .filter((value) => value.title && String(value.title).trim().length)
                                        .map((value, valueIndex) => {
                                            const valueKey = value.id
                                                ? `${attributeKey}_${value.id}`
                                                : `${attributeKey}_new_${valueIndex}`;

                                            return {
                                                key: valueKey,
                                                id: value.id,
                                                title: value.title,
                                            };
                                        }),
                                };
                            })
                            .filter((attribute) => attribute.values.length > 0);

                        return [
                            ...globalAttributes,
                            ...customAttributes,
                        ];
                    },

                    addSaleItem(payload = {}) {
                        this.saleItems.push({
                            __key: this.makeKey('sale-item'),
                            id: payload.id ?? null,
                            sku: payload.sku ?? '',
                            base_price: payload.base_price ?? '',
                            status: payload.status ?? 'available',
                            download_limit: payload.download_limit ?? '',
                            download_expires_in_days: payload.download_expires_in_days ?? '',
                            max_quantity_per_order: payload.max_quantity_per_order ?? '',
                            description: payload.description ?? '',
                            attribute_values: payload.attribute_values ?? {},
                            files: this.normalizeSaleItemFiles(payload.files ?? []),
                            _open: true,
                        });
                    },

                    syncSaleItemTitleFromCombination(item) {
                        if (item.title_manually_changed && item.title) {
                            return;
                        }

                        const label = this.saleItemCombinationLabel(item);

                        if (!label) {
                            return;
                        }

                        item.title = label;
                        item.title_auto_generated = true;
                    },

                    removeSaleItem(index) {
                        this.saleItems.splice(index, 1);
                    },

                    addSaleItemFile(item) {
                        item.files.push({
                            __key: this.makeKey('sale-item-file'),
                            id: null,
                            title: '',
                            url: '',
                            sort_order: item.files.length,
                        });
                    },

                    removeSaleItemFile(item, fileIndex) {
                        item.files.splice(fileIndex, 1);
                    },

                    saleItemHeading(item, index) {
                        if (item.title && String(item.title).trim().length) {
                            return item.title;
                        }

                        const combinationLabel = this.saleItemCombinationLabel(item);

                        if (combinationLabel) {
                            return combinationLabel;
                        }

                        return `آیتم فروش ${index + 1}`;
                    },

                    saleItemCombinationLabel(item) {
                        const parts = [];

                        this.saleItemVariationAttributes.forEach((attribute) => {
                            const selectedValueKey = String(item.attribute_values?.[attribute.key] ?? '');

                            if (!selectedValueKey) {
                                return;
                            }

                            const selectedValue = attribute.values.find((value) => {
                                return String(value.key) === selectedValueKey;
                            });

                            if (!selectedValue) {
                                return;
                            }

                            parts.push(`${attribute.title}: ${selectedValue.title}`);
                        });

                        return parts.join(' / ');
                    },

                    generateAllSaleItemCombinations() {
                        const attributes = this.saleItemVariationAttributes;

                        if (attributes.length === 0) {
                            return;
                        }

                        const combinations = this.makeCombinations(attributes);

                        combinations.forEach((combination) => {
                            const attributeValues = {};

                            combination.forEach((item) => {
                                attributeValues[String(item.attribute.key)] = String(item.value.key);
                            });

                            if (this.saleItemCombinationExists(attributeValues)) {
                                return;
                            }

                            this.addSaleItem({
                                attribute_values: attributeValues,
                            });
                        });
                    },

                    makeCombinations(attributes) {
                        return attributes.reduce((carry, attribute) => {
                            const rows = [];

                            carry.forEach((existingCombination) => {
                                attribute.values.forEach((value) => {
                                    rows.push([
                                        ...existingCombination,
                                        {
                                            attribute,
                                            value,
                                        },
                                    ]);
                                });
                            });

                            return rows;
                        }, [[]]);
                    },

                    saleItemCombinationExists(attributeValues) {
                        return this.saleItems.some((item) => {
                            return this.areAttributeValuesEqual(item.attribute_values ?? {}, attributeValues);
                        });
                    },

                    areAttributeValuesEqual(first, second) {
                        const normalizedFirst = this.normalizeSaleItemAttributeValues(first);
                        const normalizedSecond = this.normalizeSaleItemAttributeValues(second);

                        const firstKeys = Object.keys(normalizedFirst).sort();
                        const secondKeys = Object.keys(normalizedSecond).sort();

                        if (firstKeys.length !== secondKeys.length) {
                            return false;
                        }

                        return firstKeys.every((key, index) => {
                            return key === secondKeys[index]
                                && String(normalizedFirst[key]) === String(normalizedSecond[key]);
                        });
                    },

                    addSimpleFile() {
                        this.simpleFiles.push({
                            __key: this.makeKey('simple-file'),
                            id: null,
                            title: '',
                            url: '',
                            sort_order: this.simpleFiles.length,
                        });
                    },

                    removeSimpleFile(fileIndex) {
                        this.simpleFiles.splice(fileIndex, 1);
                    },

                    normalizeProductAddons(items) {
                        return (items ?? []).map((item) => {
                            return {
                                __key: item.__key ?? this.makeKey('product-addon'),
                                id: item.id ?? null,
                                title: item.title ?? '',
                                description: item.description ?? '',
                                price: item.price ?? 0,
                                max_quantity_per_order: item.max_quantity_per_order ?? 1,
                                requires_purchase: this.normalizeBoolean(item.requires_purchase, true),
                                is_active: this.normalizeBoolean(item.is_active, true),
                                sort_order: item.sort_order ?? 0,
                                _open: this.normalizeBoolean(item._open, false),
                            };
                        });
                    },
                };
            }
        </script>
    @endpush
@endonce
