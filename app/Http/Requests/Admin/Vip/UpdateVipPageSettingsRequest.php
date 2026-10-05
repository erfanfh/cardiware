<?php

namespace App\Http\Requests\Admin\Vip;

use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVipPageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(VipSettings::DEFAULT_HERO) as $key) {
            $rules[$key] = ['required', 'string', 'max:'.($key === 'hero_description' ? 1000 : 160)];
        }

        $rules['featured_plan_id'] = [
            'nullable',
            'integer',
            Rule::exists('vip_plans', 'id')->where('is_active', true)->whereNull('deleted_at'),
        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        foreach (array_keys(VipSettings::DEFAULT_HERO) as $key) {
            if (is_string($this->input($key))) {
                $values[$key] = trim($this->input($key));
            }
        }

        $values['featured_plan_id'] = $this->input('featured_plan_id') === ''
            ? null
            : $this->input('featured_plan_id');

        $this->merge($values);
    }
}
