<?php

namespace App\Http\Requests\Api\V1\Identity;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithLauncherOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'max:20'],
            'otp_code' => [
                'required',
                'string',
                'regex:/^\d{' . (int)config('auth-flow.otp.length', 6) . '}$/',
            ],
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
        ];
    }
}
