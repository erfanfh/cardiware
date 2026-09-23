<?php

namespace App\Http\Requests\Api\V1\Launcher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LauncherBootstrapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version' => [
                'required',
                'string',
                'max:30',
                'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/',
            ],

            'platform' => [
                'required',
                'string',
                Rule::in(['windows']),
            ],

            'channel' => [
                'sometimes',
                'string',
                Rule::in(['stable']),
            ],

            // Optional and not authentication: a returning launcher sends its own
            // locally-generated device id (see DeviceIdentityService on the client)
            // so a maintenance-bypassed tester's build can be told apart from
            // everyone else's, without requiring the login this endpoint runs
            // before. A missing/unrecognized value just means "no bypass applies",
            // same as an old launcher build that never sends it at all.
            'device_id' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'version' => 'نسخه لانچر',
            'platform' => 'پلتفرم',
            'channel' => 'کانال انتشار',
            'device_id' => 'شناسه دستگاه',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'channel' => $this->input('channel', 'stable'),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'version' => [
                'description' => 'The currently installed launcher version.',
                'example' => '1.0.0',
            ],

            'platform' => [
                'description' => 'The launcher operating system.',
                'example' => 'windows',
            ],

            'channel' => [
                'description' => 'The launcher release channel.',
                'example' => 'stable',
            ],
        ];
    }
}
