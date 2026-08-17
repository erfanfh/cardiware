<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRewardOfferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rewardOffer = $this->route('reward_offer');
        $rewardOfferId = is_object($rewardOffer) ? $rewardOffer->getKey() : $rewardOffer;

        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:80', 'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('reward_offers', 'code')->ignore($rewardOfferId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(RewardTypeEnum::class)],
            'points_cost' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'redemption_limit' => ['nullable', 'integer', 'min:1'],
            'redemption_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'config' => ['required', 'array'],
            'config.coupon_type' => ['required', Rule::enum(CouponTypeEnum::class)],
            'config.coupon_value' => ['required', 'integer', 'min:1'],
            'config.max_discount_amount' => ['nullable', 'integer', 'min:1'],
            'config.minimum_order_amount' => ['required', 'integer', 'min:0'],
            'config.expires_after_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'config.applies_to_addons' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان پاداش',
            'code' => 'کد داخلی پاداش',
            'description' => 'توضیحات پاداش',
            'type' => 'نوع پاداش',
            'points_cost' => 'هزینه امتیازی',
            'is_active' => 'وضعیت',
            'sort_order' => 'ترتیب نمایش',
            'starts_at' => 'زمان شروع',
            'ends_at' => 'زمان پایان',
            'redemption_limit' => 'ظرفیت کل دریافت',
            'redemption_limit_per_user' => 'سقف دریافت هر کاربر',
            'config.coupon_type' => 'نوع تخفیف',
            'config.coupon_value' => 'مقدار تخفیف',
            'config.max_discount_amount' => 'حداکثر مبلغ تخفیف',
            'config.minimum_order_amount' => 'حداقل مبلغ سفارش',
            'config.expires_after_days' => 'مدت اعتبار کد',
            'config.applies_to_addons' => 'اعمال روی خدمات افزوده',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (
                $this->input('config.coupon_type') === CouponTypeEnum::PERCENTAGE->value
                && (int)$this->input('config.coupon_value') > 100
            ) {
                $validator->errors()->add('config.coupon_value', 'در تخفیف درصدی، مقدار تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.');
            }

            $globalLimit = $this->input('redemption_limit');
            $perUserLimit = $this->input('redemption_limit_per_user');
            if ($globalLimit !== null && $perUserLimit !== null && (int)$perUserLimit > (int)$globalLimit) {
                $validator->errors()->add('redemption_limit_per_user', 'سقف دریافت هر کاربر نمی‌تواند بیشتر از ظرفیت کل پاداش باشد.');
            }

            $rewardOffer = $this->route('reward_offer');
            if (is_object($rewardOffer) && $globalLimit !== null && (int)$globalLimit < (int)$rewardOffer->redeemed_count) {
                $validator->errors()->add('redemption_limit', 'ظرفیت کل نمی‌تواند کمتر از تعداد دریافت‌های ثبت شده قبلی باشد.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $config = is_array($this->input('config')) ? $this->input('config') : [];

        foreach (['coupon_value', 'max_discount_amount', 'minimum_order_amount', 'expires_after_days'] as $key) {
            if (array_key_exists($key, $config)) {
                $config[$key] = $this->normalizeNullableInteger($config[$key], $key === 'minimum_order_amount' ? 0 : null);
            }
        }

        $config['applies_to_addons'] = filter_var(
            $config['applies_to_addons'] ?? false,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? false;

        if (($config['coupon_type'] ?? null) === CouponTypeEnum::FIXED_AMOUNT->value) {
            $config['max_discount_amount'] = null;
        }

        $this->merge([
            'title' => is_string($this->input('title')) ? trim($this->input('title')) : $this->input('title'),
            'code' => is_string($this->input('code')) ? strtolower(trim($this->input('code'))) : $this->input('code'),
            'description' => is_string($this->input('description')) ? trim($this->input('description')) : $this->input('description'),
            'points_cost' => $this->normalizeNullableInteger($this->input('points_cost')),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->normalizeNullableInteger($this->input('sort_order'), 0),
            'starts_at' => $this->normalizeDateTime($this->input('starts_at')),
            'ends_at' => $this->normalizeDateTime($this->input('ends_at')),
            'redemption_limit' => $this->normalizeNullableInteger($this->input('redemption_limit')),
            'redemption_limit_per_user' => $this->normalizeNullableInteger($this->input('redemption_limit_per_user')),
            'config' => $config,
        ]);
    }

    private function normalizeNullableInteger(mixed $value, ?int $default = null): ?int
    {
        return ($value === null || $value === '') ? $default : (int)$value;
    }

    private function normalizeDateTime(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
