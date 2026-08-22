<?php

namespace App\Http\Requests\Front\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class ClaimDrawPrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'regex:/^(?:\+?98|0098|0)?9\d{9}$/'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'address' => ['required', 'string', 'min:10', 'max:1000'],
            'accept_rules' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_name.required' => 'نام تحویل‌گیرنده را وارد کنید.',
            'mobile.required' => 'شماره موبایل تحویل‌گیرنده را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'postal_code.regex' => 'کد پستی باید ۱۰ رقم باشد.',
            'address.required' => 'نشانی تحویل جایزه را وارد کنید.',
            'address.min' => 'نشانی تحویل جایزه کامل نیست.',
            'accept_rules.accepted' => 'پذیرش قوانین دریافت جایزه الزامی است.',
        ];
    }
}
