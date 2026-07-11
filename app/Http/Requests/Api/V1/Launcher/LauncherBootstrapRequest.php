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
        ];
    }

    public function attributes(): array
    {
        return [
            'version' => 'نسخه لانچر',
            'platform' => 'پلتفرم',
            'channel' => 'کانال انتشار',
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
