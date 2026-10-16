<?php

namespace App\Http\Requests\Admin\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class UpdateSmsNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('setting.general');
    }

    public function rules(): array
    {
        return [
            'ticket_reply_sms_enabled' => ['required', 'boolean'],
            'order_paid_sms_enabled' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ticket_reply_sms_enabled' => 'پیامک پاسخ تیکت',
            'order_paid_sms_enabled' => 'پیامک خرید موفق',
        ];
    }
}
