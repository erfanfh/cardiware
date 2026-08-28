<?php

namespace App\Http\Requests\Front\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class DeclineDrawPrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
            'confirm_decline' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_decline.accepted' => 'برای انصراف باید انتقال جایزه به نفر ذخیره را تأیید کنید.',
        ];
    }
}
