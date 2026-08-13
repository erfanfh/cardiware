<?php

namespace App\Http\Requests\Admin\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbandonedCartSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'guest_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'user_retention_months' => ['required', 'integer', 'min:1', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_retention_days.required' => 'مدت نگهداری سبد کاربران مهمان را وارد کنید.',
            'guest_retention_days.integer' => 'مدت نگهداری سبد کاربران مهمان باید عدد صحیح باشد.',
            'guest_retention_days.min' => 'مدت نگهداری سبد کاربران مهمان نمی‌تواند کمتر از یک روز باشد.',
            'guest_retention_days.max' => 'مدت نگهداری سبد کاربران مهمان نمی‌تواند بیشتر از ۳۶۵ روز باشد.',
            'user_retention_months.required' => 'مدت نگهداری سبد کاربران عضو را وارد کنید.',
            'user_retention_months.integer' => 'مدت نگهداری سبد کاربران عضو باید عدد صحیح باشد.',
            'user_retention_months.min' => 'مدت نگهداری سبد کاربران عضو نمی‌تواند کمتر از یک ماه باشد.',
            'user_retention_months.max' => 'مدت نگهداری سبد کاربران عضو نمی‌تواند بیشتر از ۱۲۰ ماه باشد.',
        ];
    }
}
