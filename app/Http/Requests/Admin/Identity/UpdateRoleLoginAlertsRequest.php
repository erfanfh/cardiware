<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class UpdateRoleLoginAlertsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('role.edit');
    }

    public function rules(): array
    {
        return [
            'login_email_enabled' => ['required', 'boolean'],
            'login_sms_enabled' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login_email_enabled' => 'اعلان ورود با ایمیل',
            'login_sms_enabled' => 'اعلان ورود با پیامک',
        ];
    }
}
