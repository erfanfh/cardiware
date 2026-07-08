<?php

namespace App\Http\Requests\Api\V1\Identity;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],

            // فعلا فقط نام دستگاه را می‌گیریم.
            // بعدا device_uuid و app_version هم اضافه می‌کنیم.
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => 'ایمیل یا موبایل',
            'password' => 'رمز عبور',
            'device_name' => 'نام دستگاه',
        ];
    }
}
