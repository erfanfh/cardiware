<?php

namespace App\Http\Requests\Admin\Loyalty;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLoyaltySettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // فیلد مایل‌استون‌های زنجیره از فرم به‌صورت رشته JSON می‌آید؛
        // قبل از اعتبارسنجی به آرایه تبدیل می‌شود تا ArrayCast مستقیماً آن را ذخیره کند.
        if ($this->has('streak_milestones') && is_string($this->input('streak_milestones'))) {
            $decoded = json_decode(trim($this->input('streak_milestones')), true);

            $this->merge(['streak_milestones' => is_array($decoded) ? $decoded : null]);
        }
    }

    public function authorize(): bool
    {
        return Gate::allows('loyalty-rule.list');
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'point_conversion_enabled' => ['required', 'boolean'],
            'point_conversion_points_per_unit' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'point_conversion_wallet_amount_per_unit' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'point_conversion_minimum_points' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'point_conversion_maximum_points_per_request' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'point_conversion_daily_points_limit' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'max_credit_percent_per_order' => ['required', 'integer', 'min:0', 'max:100'],
            'streak_milestones' => ['required', 'array', 'min:1'],
            'points_inactivity_expiry_months' => ['required', 'integer', 'min:0', 'max:240'],
            'vip_points_bonus_percent' => ['required', 'integer', 'min:0', 'max:500'],
            'champion_prizes' => ['required', 'array'],
            'champion_prizes.*' => ['array', 'size:3'],
            'champion_prizes.*.*.points' => ['required', 'integer', 'min:0', 'max:10000000'],
            'champion_prizes.*.*.coupon_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'champion_prizes.*.*.coupon_max_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'champion_prizes.*.*.coupon_days' => ['required', 'integer', 'min:1', 'max:365'],
            'champion_prizes.*.*.raffle_tickets' => ['required', 'integer', 'min:0', 'max:10000'],
            'champion_year_grand_prize' => ['nullable', 'string', 'max:190'],
            'champion_year_min_xp' => ['required', 'integer', 'min:0', 'max:100000000'],
            'champion_year_min_missions' => ['required', 'integer', 'min:0', 'max:10000'],
            'champion_year_min_orders' => ['required', 'integer', 'min:0', 'max:10000'],
            'club_raffle_campaign_id' => ['nullable', 'integer', 'min:0'],
            'purchase_raffle_toman_per_ticket' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'elite_min_level_id' => ['nullable', 'integer', 'min:0'],
            'streak_milestones.*.days' => ['required_with:streak_milestones', 'integer', 'min:1', 'max:3650'],
            'streak_milestones.*.points' => ['required_with:streak_milestones', 'integer', 'min:0', 'max:1000000'],
            'streak_milestones.*.repeat' => ['nullable', 'boolean'],
            'streak_milestones.*.vip_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'registration_gift_enabled' => ['required', 'boolean'],
            'registration_gift_reward_offer_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => $this->boolean('registration_gift_enabled')),
                Rule::exists('reward_offers', 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->whereNull('deleted_at')
                        ->where('is_active', true),
                ),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $unit = $this->integer('point_conversion_points_per_unit');
                $minimum = $this->integer('point_conversion_minimum_points');
                $maximum = $this->integer('point_conversion_maximum_points_per_request');
                $daily = $this->integer('point_conversion_daily_points_limit');

                if ($unit <= 0) {
                    return;
                }

                $this->validateMultiple($validator, 'point_conversion_minimum_points', $minimum, $unit);

                if ($minimum < $unit) {
                    $validator->errors()->add(
                        'point_conversion_minimum_points',
                        'حداقل تبدیل نمی‌تواند کمتر از واحد تبدیل باشد.',
                    );
                }

                foreach ([
                    'point_conversion_maximum_points_per_request' => $maximum,
                    'point_conversion_daily_points_limit' => $daily,
                ] as $field => $value) {
                    if ($value === 0) {
                        continue;
                    }

                    $this->validateMultiple($validator, $field, $value, $unit);

                    if ($value < $minimum) {
                        $validator->errors()->add(
                            $field,
                            'این سقف باید صفر (نامحدود) یا بیشتر از حداقل تبدیل باشد.',
                        );
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'point_conversion_enabled' => 'وضعیت تبدیل امتیاز',
            'point_conversion_points_per_unit' => 'امتیاز هر واحد تبدیل',
            'point_conversion_wallet_amount_per_unit' => 'اعتبار هر واحد تبدیل',
            'point_conversion_minimum_points' => 'حداقل امتیاز',
            'point_conversion_maximum_points_per_request' => 'سقف هر درخواست',
            'point_conversion_daily_points_limit' => 'سقف روزانه',
            'registration_gift_enabled' => 'وضعیت هدیه ثبت‌نام',
            'vip_points_bonus_percent' => 'پاداش امتیاز اشتراک ویژه',
            'registration_gift_reward_offer_id' => 'پاداش هدیه ثبت‌نام',
        ];
    }

    private function validateMultiple(
        Validator $validator,
        string $field,
        int $value,
        int $unit,
    ): void {
        if ($value % $unit !== 0) {
            $validator->errors()->add(
                $field,
                "این مقدار باید مضربی از {$unit} باشد.",
            );
        }
    }
}
