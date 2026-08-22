<?php

namespace App\Http\Requests\Admin\Promotion;

use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');

        $couponId = is_object($coupon)
            ? $coupon->getKey()
            : $coupon;

        return [
            'status' => [
                'required',
                Rule::enum(CouponStatusEnum::class),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Z0-9\-_.]+$/',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'type' => [
                'required',
                Rule::enum(CouponTypeEnum::class),
            ],

            'value' => [
                'required',
                'integer',
                'min:1',
            ],

            'max_discount_amount' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'minimum_order_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'usage_limit_per_user' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'is_floating_box_enabled' => [
                'required',
                'boolean',
            ],

            'applies_to_all_users' => [
                'required',
                'boolean',
            ],

            'user_ids' => [
                Rule::requiredIf(fn() => !$this->boolean('applies_to_all_users')),
                'array',
            ],

            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id'),
            ],

            'scope' => [
                'required',
                Rule::enum(CouponScopeEnum::class),
            ],

            'product_ids' => [
                Rule::requiredIf(fn() => $this->input('scope') === CouponScopeEnum::PRODUCTS->value),
                'array',
            ],

            'product_ids.*' => [
                'integer',
                Rule::exists('products', 'id'),
            ],

            'store_id' => [
                Rule::requiredIf(fn() => $this->input('scope') === CouponScopeEnum::STORE->value),
                'nullable',
                'integer',
                Rule::exists('stores', 'id'),
            ],

            'applies_to_addons' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'وضعیت',
            'title' => 'عنوان',
            'code' => 'کد تخفیف',
            'description' => 'توضیحات',
            'type' => 'نوع تخفیف',
            'value' => 'مقدار تخفیف',
            'max_discount_amount' => 'حداکثر مبلغ تخفیف',
            'minimum_order_amount' => 'حداقل مبلغ سفارش',
            'starts_at' => 'زمان شروع',
            'expires_at' => 'زمان پایان',
            'usage_limit' => 'تعداد استفاده کلی',
            'usage_limit_per_user' => 'تعداد استفاده برای هر کاربر',
            'is_floating_box_enabled' => 'باکس شناور تخفیف',
            'applies_to_all_users' => 'قابل استفاده برای همه کاربران',
            'user_ids' => 'کاربران خاص',
            'scope' => 'محدوده اعمال',
            'product_ids' => 'محصولات مجاز',
            'store_id' => 'فروشنده مجاز',
            'applies_to_addons' => 'اعمال روی خدمات افزوده',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('type') === CouponTypeEnum::PERCENTAGE->value) {
                $value = (int)$this->input('value');

                if ($value > 100) {
                    $validator->errors()->add('value', 'در تخفیف درصدی، مقدار تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.');
                }
            }

            if (
                !$this->boolean('applies_to_all_users')
                && empty($this->input('user_ids', []))
            ) {
                $validator->errors()->add('user_ids', 'وقتی کدتخفیف عمومی نیست، انتخاب حداقل یک کاربر الزامی است.');
            }

            if (
                $this->input('scope') === CouponScopeEnum::PRODUCTS->value
                && empty($this->input('product_ids', []))
            ) {
                $validator->errors()->add('product_ids', 'برای محدودیت روی محصولات خاص، انتخاب حداقل یک محصول الزامی است.');
            }

            if (
                $this->input('scope') === CouponScopeEnum::STORE->value
                && blank($this->input('store_id'))
            ) {
                $validator->errors()->add('store_id', 'برای محدودیت روی فروشنده خاص، انتخاب فروشنده الزامی است.');
            }

            if ($this->boolean('is_floating_box_enabled') && blank($this->input('usage_limit'))) {
                $validator->errors()->add(
                    'usage_limit',
                    'برای نمایش باکس شناور تخفیف، تعیین تعداد استفاده کلی الزامی است.'
                );
            }

            if ($this->boolean('is_floating_box_enabled') && !$this->boolean('applies_to_all_users')) {
                $validator->errors()->add(
                    'is_floating_box_enabled',
                    'باکس عمومی تخفیف فقط برای کدهای قابل استفاده توسط همه کاربران فعال می‌شود.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->normalizeCode($this->input('code')),

            'applies_to_all_users' => $this->boolean('applies_to_all_users'),
            'applies_to_addons' => $this->boolean('applies_to_addons'),
            'is_floating_box_enabled' => $this->boolean('is_floating_box_enabled'),

            'starts_at' => $this->normalizeDateTime($this->input('starts_at')),
            'expires_at' => $this->normalizeDateTime($this->input('expires_at')),

            'minimum_order_amount' => $this->normalizeNullableInteger(
                $this->input('minimum_order_amount'),
                default: 0
            ),

            'value' => $this->normalizeNullableInteger($this->input('value')),
            'max_discount_amount' => $this->normalizeNullableInteger($this->input('max_discount_amount')),
            'usage_limit' => $this->normalizeNullableInteger($this->input('usage_limit')),
            'usage_limit_per_user' => $this->normalizeNullableInteger($this->input('usage_limit_per_user')),

            'user_ids' => $this->normalizeIdArray($this->input('user_ids', [])),
            'product_ids' => $this->normalizeIdArray($this->input('product_ids', [])),
            'store_id' => $this->filled('store_id') ? (int)$this->input('store_id') : null,
        ]);
    }

    private function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return strtoupper(trim($code));
    }

    private function normalizeNullableInteger(mixed $value, ?int $default = null): ?int
    {
        if (blank($value)) {
            return $default;
        }

        return (int)$value;
    }

    private function normalizeIdArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn($item) => filled($item))
            ->map(fn($item) => (int)$item)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
