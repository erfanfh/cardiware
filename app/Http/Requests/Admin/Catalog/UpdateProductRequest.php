<?php

namespace App\Http\Requests\Admin\Catalog;

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
            'sale_items.*.base_price' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.status' => [
                'nullable',
                Rule::in(array_column(ProductSaleItemStatusEnum::cases(), 'value')),
            ],
            'sale_items.*.download_limit' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.download_expires_in_days' => ['nullable', 'integer', 'min:0'],
            'sale_items.*.max_quantity_per_order' => ['nullable', 'integer', 'min:1'],
            'sale_items.*.description' => ['nullable', 'string'],

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
            'sale_items.*.discount.discounted_price' => 'مبلغ با تخفیف',
            'sale_items.*.discount.starts_at' => 'تاریخ شروع تخفیف',
            'sale_items.*.discount.ends_at' => 'تاریخ پایان تخفیف',
            'sale_items.*.discount.show_special_box' => 'نمایش باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_title' => 'عنوان باکس تخفیف ویژه',
            'sale_items.*.discount.special_box_description' => 'توضیحات باکس تخفیف ویژه',
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
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSaleItemsForValidation(): array
    {
        return collect($this->input('sale_items', []))
            ->filter(fn($item) => is_array($item))
            ->map(function (array $item): array {
                $discount = data_get($item, 'discount', []);

                if (!is_array($discount)) {
                    $discount = [];
                }

                $item['discount'] = [
                    'id' => filled(data_get($discount, 'id'))
                        ? (int)data_get($discount, 'id')
                        : null,

                    'discounted_price' => $this->normalizeIntegerInput(
                        data_get($discount, 'discounted_price')
                    ),

                    'starts_at' => $this->normalizeJalaliDateTimeInput(
                        data_get($discount, 'starts_at')
                    ),

                    'ends_at' => $this->normalizeJalaliDateTimeInput(
                        data_get($discount, 'ends_at')
                    ),

                    'show_special_box' => $this->normalizeBooleanInput(
                        data_get($discount, 'show_special_box'),
                        false
                    ),

                    'special_box_title' => filled(data_get($discount, 'special_box_title'))
                        ? trim((string)data_get($discount, 'special_box_title'))
                        : null,

                    'special_box_description' => filled(data_get($discount, 'special_box_description'))
                        ? trim((string)data_get($discount, 'special_box_description'))
                        : null,
                ];

                return $item;
            })
            ->values()
            ->all();
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
