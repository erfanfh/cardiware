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
            'device.public_key' => ['required', 'string', 'max:2048'],
            'device.public_key_fingerprint' => ['required', 'regex:/^[a-f0-9]{64}$/'],
            'device.proof_timestamp' => ['required', 'integer'],
            'device.proof_nonce' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9+\/_=-]+$/'],
            'device.proof_signature' => ['required', 'string', 'max:512', 'regex:/^[A-Za-z0-9+\/_=-]+$/'],
            'device.platform' => ['required', 'in:windows'],
            'device.app_version' => [
                'required',
                'string',
                'max:30',
                'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/',
            ],
            'device_transfer_code' => [
                'nullable',
                'string',
                'regex:/^\d{' . (int)config('auth-flow.otp.length', 6) . '}$/',
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
                    'public_key' => 'base64-subject-public-key-info',
                    'public_key_fingerprint' => str_repeat('a', 64),
                    'proof_timestamp' => 1760000000,
                    'proof_nonce' => 'base64-random-nonce',
                    'proof_signature' => 'base64-ecdsa-signature',
                    'platform' => 'windows',
                    'app_version' => '1.0.0',
                ],
            ],
        ];
    }
}
