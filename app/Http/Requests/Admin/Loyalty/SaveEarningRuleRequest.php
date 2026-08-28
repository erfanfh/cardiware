<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Application\Loyalty\Earning\Support\EarningRuleDefinitionCatalog;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEarningRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $earningRule = $this->route('earning_rule');
        $earningRuleId = is_object($earningRule) ? $earningRule->getKey() : $earningRule;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('earning_rules', 'code')->ignore($earningRuleId),
            ],
            'type' => ['required', Rule::enum(EarningRuleTypeEnum::class)],
            'is_active' => ['required', 'boolean'],
            'priority' => ['required', 'integer', 'min:0', 'max:65535'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'config' => ['required', 'array'],
        ];

        $type = EarningRuleTypeEnum::tryFrom((string)$this->input('type'));

        if ($type) {
            $definitions = app(EarningRuleDefinitionCatalog::class);

            foreach ($definitions->validationRules($type) as $key => $configRules) {
                $rules["config.{$key}"] = $configRules;
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان قانون',
            'code' => 'کد قانون',
            'type' => 'نوع قانون',
            'is_active' => 'وضعیت قانون',
            'priority' => 'اولویت',
            'starts_at' => 'زمان شروع',
            'ends_at' => 'زمان پایان',
            'config' => 'تنظیمات قانون',
            'config.points' => 'امتیاز',
            'config.amount_step' => 'گام مبلغ خرید',
            'config.minimum_order_amount' => 'حداقل مبلغ سفارش',
            'config.max_points_per_order' => 'حداکثر امتیاز هر سفارش',
            'config.daily_limit' => 'حداکثر تعداد امتیازدهی روزانه',
            'config.include_replies' => 'محاسبه پاسخ دیدگاه',
            'config.minimum_membership_years' => 'حداقل سابقه عضویت',
            'config.required_seconds' => 'حداقل زمان حضور',
            'config.targets' => 'نوع محتوای قابل امتیاز',
            'config.targets.*' => 'نوع محتوای قابل امتیاز',
            'config.unique_target_daily' => 'محدودیت هر محتوا',
        ];
    }

    protected function prepareForValidation(): void
    {
        $config = is_array($this->input('config')) ? $this->input('config') : [];

        foreach (['points', 'amount_step', 'minimum_order_amount', 'max_points_per_order', 'daily_limit', 'minimum_membership_years', 'required_seconds'] as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $config[$key] = $this->normalizeNullableInteger($config[$key]);
        }

        if (array_key_exists('unique_target_daily', $config)) {
            $config['unique_target_daily'] = filter_var($config['unique_target_daily'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('include_replies', $config)) {
            $config['include_replies'] = filter_var(
                $config['include_replies'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ) ?? false;
        }

        $this->merge([
            'title' => is_string($this->input('title')) ? trim($this->input('title')) : $this->input('title'),
            'code' => $this->normalizeCode($this->input('code')),
            'is_active' => $this->boolean('is_active'),
            'priority' => $this->normalizeNullableInteger($this->input('priority')) ?? 100,
            'starts_at' => $this->normalizeDateTime($this->input('starts_at')),
            'ends_at' => $this->normalizeDateTime($this->input('ends_at')),
            'config' => $config,
        ]);
    }

    private function normalizeCode(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return strtolower(trim($value));
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }

    private function normalizeDateTime(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
