@once
    @push('scripts')
        <script>
            function productInformationBox(config) {
                return {
                    activeTab: config.defaultTab ?? 'basic',

                    productType: config.values?.type ?? config.enums.types.digital,
                    productStructure: config.values?.structure ?? config.enums.structures.simple,
                    productContentType: config.values?.content_type ?? '',

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

                        if (this.saleItems.length === 0) {
                            this.saleItems = this.normalizeSaleItems([{}]);
                        }

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

                    defaultSaleItemManifest() {
                        return {
                            /*
                             * Empty means automatic detection by the backend.
                             */
                            download_type: '',

                            archive_format: '',
                            assembled_file_name: '',

                            install_directory: '',
                            install_folder_name: '',
                            has_option_file: false,
                            requires_administrator: false,

                            main_executable_path: '',
                            working_directory: '.',
                            launch_arguments: '',

                            requires_sider: false,
                            sider_executable_path: '',
                            sider_working_directory: '.',
                            sider_arguments: '',
                            run_sider_as_administrator: false,

                            cpu_compatibility: {
                                enabled: false,
                                default_mode: 'off',
                                apply_to_game: false,
                                apply_to_sider: false,
                                allow_user_override: false,
                            },

                            capabilities: {
                                supports_install: true,
                                supports_launch: true,
                                supports_sider: false,
                                supports_settings: false,
                                supports_profiles: false,
                                supports_update: false,
                                supports_mod_manager: false,
                            },

                            install_options: {
                                extract_enabled: true,
                                delete_archives_after_install: true,
                                ask_before_delete_archives: true,
                                clean_temp_after_install: true,
                                create_desktop_shortcut: false,
                            },
                        };
                    },

                    normalizeSaleItemManifest(manifest = {}) {
                        const defaults = this.defaultSaleItemManifest();

                        const source = manifest
                        && typeof manifest === 'object'
                        && !Array.isArray(manifest)
                            ? manifest
                            : {};

                        const capabilities = source.capabilities
                        && typeof source.capabilities === 'object'
                        && !Array.isArray(source.capabilities)
                            ? source.capabilities
                            : {};

                        const installOptions = source.install_options
                        && typeof source.install_options === 'object'
                        && !Array.isArray(source.install_options)
                            ? source.install_options
                            : {};

                        const cpuCompatibility = source.cpu_compatibility
                        && typeof source.cpu_compatibility === 'object'
                        && !Array.isArray(source.cpu_compatibility)
                            ? source.cpu_compatibility
                            : {};

                        const allowedDownloadTypes = [
                            '',
                            'single_file',
                            'multiple_files',
                            'split_archive',
                        ];

                        const downloadType = String(
                            source.download_type ?? defaults.download_type
                        );

                        return {
                            ...defaults,
                            ...source,

                            download_type: allowedDownloadTypes.includes(downloadType)
                                ? downloadType
                                : '',

                            archive_format: source.archive_format ?? '',

                            assembled_file_name: source.assembled_file_name ?? '',

                            install_directory: source.install_directory ?? '',
                            install_folder_name: source.install_folder_name ?? '',
                            has_option_file: this.normalizeBoolean(source.has_option_file, false),

                            requires_administrator: this.normalizeBoolean(
                                source.requires_administrator,
                                false
                            ),

                            main_executable_path: source.main_executable_path ?? '',

                            working_directory: source.working_directory ?? '.',

                            launch_arguments: source.launch_arguments ?? '',

                            requires_sider: this.normalizeBoolean(
                                source.requires_sider,
                                false
                            ),

                            sider_executable_path: source.sider_executable_path ?? '',

                            sider_working_directory: source.sider_working_directory ?? '.',

                            sider_arguments: source.sider_arguments ?? '',

                            run_sider_as_administrator:
                                this.normalizeBoolean(
                                    source.run_sider_as_administrator,
                                    false
                                ),

                            cpu_compatibility: {
                                enabled: this.normalizeBoolean(
                                    cpuCompatibility.enabled,
                                    false
                                ),
                                default_mode: ['off', 'auto', 'manual'].includes(
                                    String(cpuCompatibility.default_mode ?? 'off')
                                )
                                    ? String(cpuCompatibility.default_mode ?? 'off')
                                    : 'off',
                                apply_to_game: this.normalizeBoolean(
                                    cpuCompatibility.apply_to_game,
                                    false
                                ),
                                apply_to_sider: this.normalizeBoolean(
                                    cpuCompatibility.apply_to_sider,
                                    false
                                ),
                                allow_user_override: this.normalizeBoolean(
                                    cpuCompatibility.allow_user_override,
                                    false
                                ),
                            },

                            capabilities: {
                                supports_install:
                                    this.normalizeBoolean(
                                        capabilities.supports_install,
                                        true
                                    ),

                                supports_launch:
                                    this.normalizeBoolean(
                                        capabilities.supports_launch,
                                        true
                                    ),

                                supports_sider:
                                    this.normalizeBoolean(
                                        capabilities.supports_sider,
                                        this.normalizeBoolean(source.requires_sider, false)
                                    ) || this.normalizeBoolean(source.requires_sider, false),

                                supports_settings:
                                    this.normalizeBoolean(
                                        capabilities.supports_settings,
                                        false
                                    ),

                                supports_profiles:
                                    this.normalizeBoolean(
                                        capabilities.supports_profiles,
                                        false
                                    ),

                                supports_update:
                                    this.normalizeBoolean(
                                        capabilities.supports_update,
                                        false
                                    ),

                                supports_mod_manager:
                                    this.normalizeBoolean(
                                        capabilities.supports_mod_manager,
                                        false
                                    ),
                            },

                            install_options: {
                                extract_enabled:
                                    this.normalizeBoolean(
                                        installOptions.extract_enabled,
                                        true
                                    ),

                                delete_archives_after_install:
                                    this.normalizeBoolean(
                                        installOptions.delete_archives_after_install,
                                        true
                                    ),

                                ask_before_delete_archives:
                                    this.normalizeBoolean(
                                        installOptions.ask_before_delete_archives,
                                        true
                                    ),

                                clean_temp_after_install:
                                    this.normalizeBoolean(
                                        installOptions.clean_temp_after_install,
                                        true
                                    ),

                                create_desktop_shortcut:
                                    this.normalizeBoolean(
                                        installOptions.create_desktop_shortcut,
                                        false
                                    ),
                            },
                        };
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
                            const title = item.title ?? '';

                            return {
                                __key: item.__key ?? this.makeKey('sale-item'),
                                id: item.id ?? null,
                                sku: item.sku ?? '',
                                title,
                                edition_key: item.edition_key ?? '',
                                version: item.version ?? '1.0.0',
                                base_price: item.base_price ?? '',
                                status: item.status ?? 'available',
                                download_limit: item.download_limit ?? '',
                                download_expires_in_days: item.download_expires_in_days ?? '',
                                max_quantity_per_order: item.max_quantity_per_order ?? '',
                                description: item.description ?? '',
                                launcher_enabled: this.normalizeBoolean(item.launcher_enabled, false),
                                launcher_title: item.launcher_title ?? '',
                                launcher_excerpt: item.launcher_excerpt ?? '',
                                launcher_hero: {
                                    id: item.launcher_hero?.id ?? null,
                                    path: item.launcher_hero?.path ?? null,
                                },
                                launcher_sort_order: item.launcher_sort_order ?? 0,
                                launcher_support_enabled: this.normalizeBoolean(item.launcher_support_enabled, false),
                                launcher_support_url: item.launcher_support_url ?? '',
                                attribute_values: this.normalizeSaleItemAttributeValues(item.attribute_values ?? {}),
                                discount: this.normalizeSaleItemDiscount(item.discount ?? {}),
                                files: this.normalizeSaleItemFiles(item.files ?? []),
                                manifest: this.normalizeSaleItemManifest(item.manifest ?? {}),
                                launcher_updates: this.normalizeLauncherUpdates(item.launcher_updates ?? []),
                                launcher_mod_groups: this.normalizeLauncherModGroups(item.launcher_mod_groups ?? []),
                                launcher_mods: this.normalizeLauncherMods(item.launcher_mods ?? []),
                                /*
                                 * Existing titles should not be overwritten
                                 * automatically by variation labels.
                                 */
                                title_manually_changed: this.normalizeBoolean(
                                    item.title_manually_changed,
                                    String(title).trim().length > 0
                                ),
                                title_auto_generated: this.normalizeBoolean(
                                    item.title_auto_generated,
                                    false
                                ),
                                _open: this.normalizeBoolean(item._open, false),
                            };
                        });
                    },

                    normalizeSaleItemDiscount(discount = {}) {
                        return {
                            id: discount?.id ?? null,
                            discounted_price: discount?.discounted_price ?? '',
                            starts_at: discount?.starts_at ?? '',
                            ends_at: discount?.ends_at ?? '',
                            show_special_box: this.normalizeBoolean(discount?.show_special_box, false),
                            special_box_title: discount?.special_box_title ?? '',
                            special_box_description: discount?.special_box_description ?? '',
                        };
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
                                file_name: file.file_name ?? '',
                                url: file.url ?? '',
                                size_bytes: file.size_bytes ?? '',
                                sha256: file.sha256 ?? '',
                                mime_type: file.mime_type ?? 'application/octet-stream',
                                part_number: file.part_number ?? '',
                                archive_group: file.archive_group ?? '',
                                is_required: this.normalizeBoolean(file.is_required, true),
                                is_active: this.normalizeBoolean(file.is_active, true),
                                sort_order: file.sort_order ?? 0,
                            };
                        });
                    },

                    normalizeLauncherPackageFiles(files) {
                        return (files ?? []).map((file) => ({
                            __key: file.__key ?? this.makeKey('launcher-package-file'),
                            id: file.id ?? null,
                            title: file.title ?? '',
                            file_name: file.file_name ?? '',
                            url: file.url ?? '',
                            size_bytes: file.size_bytes ?? '',
                            sha256: file.sha256 ?? '',
                            part_number: file.part_number ?? '',
                            is_required: this.normalizeBoolean(file.is_required, true),
                        }));
                    },

                    normalizeLauncherUpdates(items) {
                        return (items ?? []).map((item) => ({
                            __key: item.__key ?? this.makeKey('launcher-update'),
                            id: item.id ?? null,
                            version: item.version ?? '',
                            title: item.title ?? '',
                            description: item.description ?? '',
                            changelog: item.changelog ?? '',
                            is_enabled: this.normalizeBoolean(item.is_enabled, false),
                            is_mandatory: this.normalizeBoolean(item.is_mandatory, false),
                            has_option_file: this.normalizeBoolean(item.has_option_file, false),
                            published_at: item.published_at ?? '',
                            files: this.normalizeLauncherPackageFiles(item.files ?? []),
                        }));
                    },

                    normalizeLauncherModGroups(items) {
                        return (items ?? []).map((item) => ({
                            __key: item.__key ?? this.makeKey('launcher-mod-group'),
                            id: item.id ?? null,
                            key: item.key ?? '',
                            title: item.title ?? '',
                            is_exclusive: this.normalizeBoolean(item.is_exclusive, false),
                        }));
                    },

                    normalizeLauncherMods(items) {
                        return (items ?? []).map((item) => ({
                            __key: item.__key ?? this.makeKey('launcher-mod'),
                            id: item.id ?? null,
                            name: item.name ?? '',
                            slug: item.slug ?? '',
                            version: item.version ?? '',
                            group_key: item.group_key ?? '',
                            short_description: item.short_description ?? '',
                            description: item.description ?? '',
                            image_media_id: item.image_media_id ?? '',
                            size_bytes: item.size_bytes ?? '',
                            priority: item.priority ?? 0,
                            is_enabled: this.normalizeBoolean(item.is_enabled, false),
                            is_required: this.normalizeBoolean(item.is_required, false),
                            compatible_base_versions_text: item.compatible_base_versions_text
                                ?? (item.compatible_base_versions ?? []).join(','),
                            compatible_update_versions_text: item.compatible_update_versions_text
                                ?? (item.compatible_update_versions ?? []).join(','),
                            published_at: item.published_at ?? '',
                            files: this.normalizeLauncherPackageFiles(item.files ?? []),
                        }));
                    },

                    addLauncherUpdate(item) {
                        item.launcher_updates.push(this.normalizeLauncherUpdates([{}])[0]);
                    },

                    addLauncherModGroup(item) {
                        item.launcher_mod_groups.push(this.normalizeLauncherModGroups([{}])[0]);
                    },

                    addLauncherMod(item) {
                        item.launcher_mods.push(this.normalizeLauncherMods([{}])[0]);
                    },

                    addLauncherPackageFile(owner) {
                        owner.files.push(this.normalizeLauncherPackageFiles([{}])[0]);
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
                        const saleItem = {
                            __key: this.makeKey('sale-item'),
                            id: payload.id ?? null,
                            sku: payload.sku ?? '',
                            title: payload.title ?? '',
                            edition_key: payload.edition_key ?? '',
                            version: payload.version ?? '1.0.0',
                            base_price: payload.base_price ?? '',
                            status: payload.status ?? 'available',
                            download_limit: payload.download_limit ?? '',
                            download_expires_in_days: payload.download_expires_in_days ?? '',
                            max_quantity_per_order: payload.max_quantity_per_order ?? '',
                            description: payload.description ?? '',
                            launcher_enabled: this.normalizeBoolean(payload.launcher_enabled, false),
                            launcher_title: payload.launcher_title ?? '',
                            launcher_excerpt: payload.launcher_excerpt ?? '',
                            launcher_hero: {
                                id: payload.launcher_hero?.id ?? null,
                                path: payload.launcher_hero?.path ?? null,
                            },
                            launcher_sort_order: payload.launcher_sort_order ?? 0,
                            launcher_support_enabled: this.normalizeBoolean(payload.launcher_support_enabled, false),
                            launcher_support_url: payload.launcher_support_url ?? '',
                            attribute_values: this.normalizeSaleItemAttributeValues(payload.attribute_values ?? {}),
                            discount: this.normalizeSaleItemDiscount(payload.discount ?? {}),
                            files: this.normalizeSaleItemFiles(payload.files ?? []),
                            manifest: this.normalizeSaleItemManifest(payload.manifest ?? {}),
                            launcher_updates: this.normalizeLauncherUpdates(payload.launcher_updates ?? []),
                            launcher_mod_groups: this.normalizeLauncherModGroups(payload.launcher_mod_groups ?? []),
                            launcher_mods: this.normalizeLauncherMods(payload.launcher_mods ?? []),
                            title_manually_changed: this.normalizeBoolean(payload.title_manually_changed, false),
                            title_auto_generated: this.normalizeBoolean(payload.title_auto_generated, false),
                            _open: true,
                        };

                        this.saleItems.push(saleItem);

                        /*
                         * Generate a readable title when the sale item was
                         * created from a variation combination.
                         */
                        this.syncSaleItemTitleFromCombination(saleItem);
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
                            file_name: '',
                            url: '',
                            size_bytes: '',
                            sha256: '',
                            mime_type: 'application/octet-stream',
                            part_number: '',
                            archive_group: '',
                            is_required: true,
                            is_active: true,
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
