<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveRewardOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var RewardOffer|null $rewardOffer */
        $rewardOffer = $this->route('reward_offer');
        $type = RewardTypeEnum::tryFrom((string) $this->input('type'));

        $rules = [
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('reward_offers', 'code')->ignore($rewardOffer?->getKey()),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'type' => ['required', Rule::enum(RewardTypeEnum::class)],
            'points_cost' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'redemption_limit' => ['nullable', 'integer', 'min:1'],
            'redemption_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'target_id' => [
                Rule::requiredIf($type === RewardTypeEnum::RAFFLE_TICKET),
                'nullable',
                'integer',
                Rule::exists('draw_campaigns', 'id')->whereNull('deleted_at'),
            ],
            'config' => ['required', 'array'],
        ];

        if ($type === RewardTypeEnum::COUPON) {
            $couponValueRules = ['required', 'integer', 'min:1'];
            if ((string) $this->input('config.coupon_type') === 'percentage') {
                $couponValueRules[] = 'max:100';
            }

            $rules += [
                'config.coupon_type' => ['required', Rule::in(['fixed_amount', 'percentage'])],
                'config.coupon_value' => $couponValueRules,
                'config.max_discount_amount' => ['nullable', 'integer', 'min:1'],
                'config.minimum_order_amount' => ['required', 'integer', 'min:0'],
                'config.expires_after_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
                'config.applies_to_addons' => ['required', 'boolean'],
            ];
        }

        if ($type === RewardTypeEnum::RAFFLE_TICKET) {
            $rules += [
                'config.ticket_quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            ];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $global = $this->input('redemption_limit');
            $perUser = $this->input('redemption_limit_per_user');

            if ($global !== null && $perUser !== null && (int) $perUser > (int) $global) {
                $validator->errors()->add(
                    'redemption_limit_per_user',
                    'سقف دریافت هر کاربر نمی تواند بیشتر از ظرفیت کل پاداش باشد.'
                );
            }

            /** @var RewardOffer|null $rewardOffer */
            $rewardOffer = $this->route('reward_offer');
            if (!$rewardOffer) {
                return;
            }

            if ($global !== null && (int) $global < (int) $rewardOffer->redeemed_count) {
                $validator->errors()->add(
                    'redemption_limit',
                    'ظرفیت کل نمی تواند کمتر از تعداد دریافت های موفق فعلی باشد.'
                );
            }

            $hasHistoricalRedemptions = $rewardOffer->redemptions()->exists();
            if (!$hasHistoricalRedemptions) {
                return;
            }

            $newType = RewardTypeEnum::tryFrom((string) $this->input('type'));
            if ($newType && $newType !== $rewardOffer->type) {
                $validator->errors()->add(
                    'type',
                    'نوع پاداش پس از ثبت اولین دریافت قابل تغییر نیست.'
                );
            }

            if (
                $rewardOffer->type === RewardTypeEnum::RAFFLE_TICKET
                && (int) $this->input('target_id') !== (int) $rewardOffer->target_id
            ) {
                $validator->errors()->add(
                    'target_id',
                    'قرعه کشی هدف پس از ثبت اولین دریافت قابل تغییر نیست.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $config = is_array($this->input('config')) ? $this->input('config') : [];
        $config['applies_to_addons'] = $this->boolean('config.applies_to_addons');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'config' => $config,
            'starts_at' => $this->normalizeDate($this->input('starts_at')),
            'ends_at' => $this->normalizeDate($this->input('ends_at')),
            'redemption_limit' => $this->nullableInteger('redemption_limit'),
            'redemption_limit_per_user' => $this->nullableInteger('redemption_limit_per_user'),
            'target_id' => $this->nullableInteger('target_id'),
        ]);
    }

    private function normalizeDate(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }

    private function nullableInteger(string $key): ?int
    {
        $value = $this->input($key);

        return $value === null || $value === ''
            ? null
            : (int) $value;
    }
}
