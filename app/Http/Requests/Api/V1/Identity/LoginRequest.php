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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'login' => [
                'description' => 'The user email address or mobile number.',
                'example' => 'launcher-test@moddingway.ir',
            ],

            'password' => [
                'description' => 'The user account password.',
                'example' => 'StrongPassword123!',
            ],

            'device_name' => [
                'description' => 'A human-readable name identifying the Windows device.',
                'example' => 'My Desktop PC',
            ],
        ];
    }
}
