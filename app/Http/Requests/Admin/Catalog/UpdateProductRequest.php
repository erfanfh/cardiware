<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domain\Catalog\Enums\ProductContentTypeEnum;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'status' => ['required', Rule::enum(ProductStatusEnum::class)],
            'type' => ['required', Rule::enum(ProductTypeEnum::class)],
            'content_type' => ['nullable', Rule::enum(ProductContentTypeEnum::class)],
            'structure' => ['required', Rule::enum(ProductStructureEnum::class)],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'published_at' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.existing' => ['nullable', 'array'],
            'tags.existing.*' => ['integer', 'exists:taxonomy_terms,id'],
            'tags.new' => ['nullable', 'array'],
            'tags.new.*' => ['string', 'max:255'],

            'category_ids' => ['nullable', 'array'],

            'image_gallery_items' => ['nullable', 'array'],
            'image_gallery_items.*' => ['array'],

            'image_gallery_items.*.id' => ['nullable', 'integer'],
            'image_gallery_items.*.title' => ['nullable', 'string', 'max:255'],
            'image_gallery_items.*.image.id' => ['nullable', 'exists:media_files,id'],
            'image_gallery_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['array'],

            'installation_step_body' => ['nullable', 'string'],

            'faqs' => ['nullable', 'array'],
            'faqs.*' => ['array'],

            'releases' => ['nullable', 'array'],
            'releases.*' => ['array'],

            'attributes' => ['nullable', 'array'],
            'attributes.*.id' => ['nullable', 'integer', 'exists:product_attribute_assignments,id'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:product_attributes,id'],
            'attributes.*.value_ids' => ['nullable', 'array'],
            'attributes.*.value_ids.*' => ['integer', 'exists:product_attribute_values,id'],
            'attributes.*.is_visible' => ['nullable', 'boolean'],
            'attributes.*.is_variation_defining' => ['nullable', 'boolean'],
            'attributes.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'custom_attributes' => ['nullable', 'array'],
            'custom_attributes.*.id' => ['nullable', 'integer', 'exists:product_custom_attributes,id'],
            'custom_attributes.*.title' => ['nullable', 'string', 'max:255'],
            'custom_attributes.*.is_visible' => ['nullable', 'boolean'],
            'custom_attributes.*.is_variation_defining' => ['nullable', 'boolean'],
            'custom_attributes.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'custom_attributes.*.values' => ['nullable', 'array'],
            'custom_attributes.*.values.*.id' => ['nullable', 'integer', 'exists:product_custom_attribute_values,id'],
            'custom_attributes.*.values.*.title' => ['nullable', 'string', 'max:255'],
            'custom_attributes.*.values.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sale_items' => ['nullable', 'array'],
            'sale_items.*.id' => ['nullable', 'integer', 'exists:product_sale_items,id'],
            'sale_items.*.sku' => ['nullable', 'string', 'max:255'],
            'sale_items.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.edition_key' => ['nullable', 'string', 'max:80'],
            'sale_items.*.version' => ['nullable', 'string', 'max:50'],
            'sale_items.*.base_price' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.status' => [
                'nullable',
                Rule::in(array_column(ProductSaleItemStatusEnum::cases(), 'value')),
            ],
            'sale_items.*.download_limit' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.download_expires_in_days' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.max_quantity_per_order' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.description' => ['nullable', 'string'],

            /*
            |--------------------------------------------------------------------------
            | Launcher manifest
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest' => ['nullable', 'array'],
            'sale_items.*.manifest.download_type' => [
                'nullable',
                Rule::in(['single_file', 'multiple_files', 'split_archive']),
            ],
            'sale_items.*.manifest.archive_format' => [
                'nullable',
                Rule::in(['7z', 'rar', 'zip']),
            ],
            'sale_items.*.manifest.assembled_file_name' => ['nullable', 'string', 'max:255'],
            'sale_items.*.manifest.install_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.requires_administrator' => ['nullable', 'boolean'],
            'sale_items.*.manifest.main_executable_path' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.working_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.launch_arguments' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.manifest.requires_sider' => ['nullable', 'boolean'],
            'sale_items.*.manifest.sider_executable_path' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.sider_working_directory' => ['nullable', 'string', 'max:500'],
            'sale_items.*.manifest.sider_arguments' => ['nullable', 'string', 'max:2000'],
            'sale_items.*.manifest.run_sider_as_administrator' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | Manifest capabilities
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.capabilities' => ['nullable', 'array'],
            'sale_items.*.manifest.capabilities.supports_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_launch' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_settings' => ['nullable', 'boolean'],
            'sale_items.*.manifest.capabilities.supports_profiles' => ['nullable', 'boolean'],

            /*
            |--------------------------------------------------------------------------
            | Manifest install options
            |--------------------------------------------------------------------------
            */
            'sale_items.*.manifest.install_options' => ['nullable', 'array'],
            'sale_items.*.manifest.install_options.extract_enabled' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.delete_archives_after_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.ask_before_delete_archives' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.clean_temp_after_install' => ['nullable', 'boolean'],
            'sale_items.*.manifest.install_options.create_desktop_shortcut' => ['nullable', 'boolean'],

            'sale_items.*.discount' => ['nullable', 'array'],
            'sale_items.*.discount.id' => ['nullable', 'integer'],
            'sale_items.*.discount.discounted_price' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.discount.starts_at' => ['nullable', 'date'],
            'sale_items.*.discount.ends_at' => ['nullable', 'date'],
            'sale_items.*.discount.show_special_box' => ['nullable', 'boolean'],
            'sale_items.*.discount.special_box_title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.discount.special_box_description' => ['nullable', 'string', 'max:1000'],

            'sale_items.*.attribute_values' => ['nullable', 'array'],
            'sale_items.*.attribute_values.*' => ['nullable', 'string', 'max:255'],

            'sale_items.*.files' => ['nullable', 'array'],
            'sale_items.*.files.*.id' => ['nullable', 'integer', 'exists:product_sale_item_files,id'],
            'sale_items.*.files.*.title' => ['nullable', 'string', 'max:255'],
            'sale_items.*.files.*.url' => ['nullable', 'url', 'max:2048'],
            'sale_items.*.files.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'addons' => ['nullable', 'array'],

            'addons.*.id' => ['nullable', 'integer', 'exists:product_addons,id'],
            'addons.*.title' => ['required', 'string', 'max:255'],
            'addons.*.description' => ['nullable', 'string'],
            'addons.*.price' => ['required', 'integer', 'min:0'],
            'addons.*.is_active' => ['nullable', 'boolean'],
            'addons.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'addons.*.metadata' => ['nullable', 'array'],

            'information_fields' => ['nullable', 'array'],
            'information_fields.*' => ['nullable', 'string', 'max:255'],

            'seo' => ['nullable', 'array'],

            'display_setting' => ['nullable', 'array'],

            'team_members' => ['nullable', 'array'],

            'update_progress' => ['nullable', 'array'],
            'update_progress.is_visible' => ['nullable', 'boolean'],
            'update_progress.progress_percentage' => ['nullable', 'integer'],
            'update_progress.started_at' => ['nullable', 'date'],
            'update_progress.release_at' => ['nullable', 'date'],

            'action_links' => ['nullable', 'array'],
            'action_links.*' => ['array'],
            'action_links.*.id' => ['nullable', 'integer'],
            'action_links.*.title' => ['nullable', 'string', 'max:120'],
            'action_links.*.url' => ['nullable', 'string', 'max:2048'],
            'action_links.*.open_in_new_tab' => ['nullable', 'boolean'],

            'medal_assignments' => ['nullable', 'array'],
            'medal_assignments.*' => ['array'],
            'medal_assignments.*.id' => ['nullable', 'integer', Rule::exists('product_medal_assignments', 'id')],
            'medal_assignments.*.product_medal_id' => ['required', 'integer', 'distinct', Rule::exists('product_medals', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان',
            'slug' => 'اسلاگ',
            'status' => 'وضعیت',
            'store_id' => 'فروشنده',
            'body' => 'توضیحات',
            'excerpt' => 'خلاصه',
            'sale_items.*.base_price' => 'قیمت پایه آیتم فروش',
            'sale_items.*.title' => 'عنوان آیتم فروش',
            'sale_items.*.edition_key' => 'کلید نسخه آیتم فروش',
            'sale_items.*.version' => 'نسخه آیتم فروش',
            'sale_items.*.discount.discounted_price' => 'مبلغ با تخفیف',
            'sale_items.*.discount.starts_at' => 'تاریخ شروع تخفیف',
            'sale_items.*.discount.ends_at' => 'تاریخ پایان تخفیف',
            'sale_items.*.discount.show_special_box' => 'نمایش باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_title' => 'عنوان باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_description' => 'توضیحات باکس تخفیف ویژه',
            'sale_items.*.manifest.download_type' => 'نوع دانلود',
            'sale_items.*.manifest.archive_format' => 'فرمت آرشیو',
            'sale_items.*.manifest.assembled_file_name' => 'نام آرشیو نهایی',
            'sale_items.*.manifest.install_directory' => 'مسیر نصب',
            'sale_items.*.manifest.main_executable_path' => 'مسیر فایل اجرایی',
            'sale_items.*.manifest.working_directory' => 'پوشه اجرا',
            'sale_items.*.manifest.launch_arguments' => 'آرگومان‌های اجرا',
            'sale_items.*.manifest.sider_executable_path' => 'مسیر فایل اجرایی Sider',
            'sale_items.*.manifest.sider_working_directory' => 'پوشه اجرای Sider',
            'sale_items.*.manifest.sider_arguments' => 'آرگومان‌های Sider',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'store_id.required' => 'انتخاب فروشنده الزامی است.',
            'store_id.exists' => 'فروشنده انتخاب‌شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $product = $this->route('product');

        $defaultStoreId = $this->input('store_id')
            ?: $product?->store_id
                ?: Store::query()->orderBy('id')->value('id');

        $tags = $this->input('tags', []);

        $existing = collect(data_get($tags, 'existing', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $new = collect(data_get($tags, 'new', []))
            ->filter(fn($title) => filled($title))
            ->map(fn($title) => preg_replace('/\s+/', ' ', trim((string)$title)))
            ->filter()
            ->unique(fn($title) => mb_strtolower($title))
            ->values()
            ->all();

        $saleItems = $this->normalizeSaleItemsForValidation();

        $this->merge([
            'store_id' => $defaultStoreId,
            'picture_id' => $this->input('picture.id'),
            'tags' => [
                'existing' => $existing,
                'new' => $new,
            ],
            'sale_items' => $saleItems,
            'published_at' => $this->input('published_at')
                ? gregorian_date($this->input('published_at'))
                : date('Y-m-d H:i:s'),
            'update_progress.started_at' => $this->input('update_progress.started_at')
                ? gregorian_date($this->input('update_progress.started_at'))
                : null,
            'update_progress.release_at' => $this->input('update_progress.release_at')
                ? gregorian_date($this->input('update_progress.release_at'))
                : null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSaleItemsForValidation(): array
    {
        return collect($this->input('sale_items', []))
            ->filter(
                static fn(mixed $item): bool => is_array($item)
            )
            ->map(function (array $item): array {
                $discount = data_get($item, 'discount', []);

                if (!is_array($discount)) {
                    $discount = [];
                }

                $item['id'] = filled(data_get($item, 'id')) ? $this->normalizeIntegerInput(data_get($item, 'id')) : null;
                $item['title'] = $this->normalizeNullableStringInput(data_get($item, 'title'), 255);
                $item['edition_key'] = $this->normalizeNullableStringInput(data_get($item, 'edition_key'), 80);
                $item['version'] = $this->normalizeNullableStringInput(data_get($item, 'version'), 50);
                $item['sku'] = $this->normalizeNullableStringInput(data_get($item, 'sku'), 255);
                $item['base_price'] = $this->normalizeIntegerInput(data_get($item, 'base_price')) ?? 0;
                $item['download_limit'] = $this->normalizeIntegerInput(data_get($item, 'download_limit'));
                $item['download_expires_in_days'] = $this->normalizeIntegerInput(data_get($item, 'download_expires_in_days'));
                $item['max_quantity_per_order'] = $this->normalizeIntegerInput(data_get($item, 'max_quantity_per_order'));
                $item['description'] = $this->normalizeNullableStringInput(data_get($item, 'description'));
                $item['sort_order'] = $this->normalizeIntegerInput(data_get($item, 'sort_order')) ?? 0;
                $item['discount'] = [
                    'id' => filled(data_get($discount, 'id')) ? $this->normalizeIntegerInput(data_get($discount, 'id')) : null,
                    'discounted_price' => $this->normalizeIntegerInput(data_get($discount, 'discounted_price')),
                    'starts_at' => $this->normalizeJalaliDateTimeInput(data_get($discount, 'starts_at')),
                    'ends_at' => $this->normalizeJalaliDateTimeInput(data_get($discount, 'ends_at')),
                    'show_special_box' => $this->normalizeBooleanInput(data_get($discount, 'show_special_box'), false),
                    'special_box_title' => $this->normalizeNullableStringInput(data_get($discount, 'special_box_title'), 255),
                    'special_box_description' => $this->normalizeNullableStringInput(data_get($discount, 'special_box_description'), 1000),
                ];
                $item['manifest'] = $this->normalizeManifestInput(data_get($item, 'manifest'));

                return $item;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeManifestInput(
        mixed $value,
    ): array
    {
        $manifest = is_array($value) ? $value : [];

        $capabilities = data_get($manifest, 'capabilities', []);

        if (!is_array($capabilities)) {
            $capabilities = [];
        }

        $installOptions = data_get($manifest, 'install_options', []);

        if (!is_array($installOptions)) {
            $installOptions = [];
        }

        return [
            /*
             * Null or empty means automatic detection.
             */
            'download_type' => $this->normalizeDownloadTypeInput(data_get($manifest, 'download_type')),
            'archive_format' => $this->normalizeArchiveFormatInput(data_get($manifest, 'archive_format')),
            'assembled_file_name' => $this->normalizeNullableStringInput(data_get($manifest, 'assembled_file_name'), 255),
            'install_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'install_directory'), 500),
            'requires_administrator' => $this->normalizeBooleanInput(data_get($manifest, 'requires_administrator'), false),
            'main_executable_path' => $this->normalizeNullableStringInput(data_get($manifest, 'main_executable_path'), 500),
            'working_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'working_directory'), 500) ?? '.',
            'launch_arguments' => $this->normalizeNullableStringInput(data_get($manifest, 'launch_arguments'), 2000),
            'requires_sider' => $this->normalizeBooleanInput(data_get($manifest, 'requires_sider'), false),
            'sider_executable_path' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_executable_path'), 500),
            'sider_working_directory' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_working_directory'), 500),
            'sider_arguments' => $this->normalizeNullableStringInput(data_get($manifest, 'sider_arguments'), 2000),
            'run_sider_as_administrator' => $this->normalizeBooleanInput(data_get($manifest, 'run_sider_as_administrator'), false),
            'capabilities' => [
                'supports_install' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_install'), true),
                'supports_launch' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_launch'), true),
                'supports_settings' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_settings'), false),
                'supports_profiles' => $this->normalizeBooleanInput(data_get($capabilities, 'supports_profiles'), false),
            ],
            'install_options' => [
                'extract_enabled' => $this->normalizeBooleanInput(data_get($installOptions, 'extract_enabled'), true),
                'delete_archives_after_install' => $this->normalizeBooleanInput(data_get($installOptions, 'delete_archives_after_install'), true),
                'ask_before_delete_archives' => $this->normalizeBooleanInput(data_get($installOptions, 'ask_before_delete_archives'), true),
                'clean_temp_after_install' => $this->normalizeBooleanInput(data_get($installOptions, 'clean_temp_after_install'), true),
                'create_desktop_shortcut' => $this->normalizeBooleanInput(data_get($installOptions, 'create_desktop_shortcut'), false),
            ],
        ];
    }

    private function normalizeDownloadTypeInput(mixed $value): ?string
    {
        $value = trim((string)$value);

        return in_array(
            $value,
            [
                'single_file',
                'multiple_files',
                'split_archive',
            ],
            true
        ) ? $value : null;
    }

    private function normalizeArchiveFormatInput(mixed $value): ?string
    {
        $value = strtolower(trim((string)$value));

        return in_array(
            $value,
            [
                '7z',
                'rar',
                'zip',
            ],
            true
        ) ? $value : null;
    }

    private function normalizeNullableStringInput(mixed $value, ?int $maxLength = null): ?string
    {
        if (!filled($value)) {
            return null;
        }

        $value = preg_replace(
            '/\s+/u',
            ' ',
            trim((string)$value)
        );

        if (!is_string($value) || $value === '') {
            return null;
        }

        if ($maxLength !== null) {
            $value = mb_substr(
                $value,
                0,
                $maxLength
            );
        }

        return $value;
    }

    private function normalizeIntegerInput(mixed $value): ?int
    {
        if (!filled($value)) {
            return null;
        }

        $value = $this->normalizeDigits((string)$value);
        $value = str_replace([',', '٬', ' '], '', $value);

        return is_numeric($value) ? max(0, (int)$value) : null;
    }

    private function normalizeBooleanInput(mixed $value, bool $fallback = false): bool
    {
        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $fallback;
    }

    private function normalizeJalaliDateTimeInput(mixed $value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        $value = $this->normalizeDigits(trim((string)$value));
        $value = str_replace('T', ' ', $value);

        return gregorian_date($value);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}
