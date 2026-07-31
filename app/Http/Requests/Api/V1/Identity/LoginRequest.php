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
            'device' => ['required', 'array'],
            'device.id' => ['required', 'uuid'],
            'device.name' => ['required', 'string', 'max:255'],
            'device.fingerprint_hash' => ['required', 'regex:/^[a-f0-9]{64}$/'],
            'device.platform' => ['required', 'in:windows'],
            'device.app_version' => [
                'required',
                'string',
                'max:30',
                'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => 'ایمیل یا موبایل',
            'password' => 'رمز عبور',
            'device' => 'دستگاه',
            'device.id' => 'شناسه دستگاه',
            'device.name' => 'نام دستگاه',
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

            'device' => [
                'description' => 'Stable Windows launcher device identity.',
                'example' => [
                    'id' => '7e02c735-794f-4c63-ae2e-98dbf5d1d4ab',
                    'name' => 'My Desktop PC',
                    'fingerprint_hash' => null,
                    'platform' => 'windows',
                    'app_version' => '1.0.0',
                ],
            ],
        ];
    }
}
