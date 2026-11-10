<?php

namespace App\Http\Requests\Admin\Vip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveVipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $plan = $this->route('vip_plan');

        return [
            'code' => $plan ? ['prohibited'] : [
                'required', 'string', 'max:80', 'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('vip_plans', 'code'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:36500'],
            'price_toman' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'purchase_discount_percent' => ['nullable', 'integer', 'min:0', 'max:90'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'کد پلن',
            'title' => 'عنوان پلن',
            'description' => 'توضیحات',
            'duration_days' => 'مدت اعتبار',
            'price_toman' => 'قیمت',
            'purchase_discount_percent' => 'درصد تخفیف خرید تکی',
            'is_active' => 'وضعیت',
            'sort_order' => 'ترتیب نمایش',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'title' => is_string($this->input('title')) ? trim($this->input('title')) : $this->input('title'),
            'description' => is_string($this->input('description')) && trim($this->input('description')) === ''
                ? null : $this->input('description'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
            'purchase_discount_percent' => $this->filled('purchase_discount_percent')
                ? $this->input('purchase_discount_percent')
                : null,
        ];

        if (! $this->route('vip_plan')) {
            $data['code'] = is_string($this->input('code'))
                ? strtolower(trim($this->input('code')))
                : $this->input('code');
        }

        $this->merge($data);
    }
}
