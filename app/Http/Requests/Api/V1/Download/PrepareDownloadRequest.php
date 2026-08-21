<?php

namespace App\Http\Requests\Api\V1\Download;

use Illuminate\Foundation\Http\FormRequest;

class PrepareDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'download_session_id' => [
                'required',
                'uuid',
            ],
            'manifest_revision' => ['required', 'integer', 'min:0'],
            'file_name' => ['required', 'string', 'max:255', 'not_regex:/[\\\\\/]/'],
            'size_bytes' => ['required', 'integer', 'min:1'],
            'sha256' => ['required', 'regex:/^[a-fA-F0-9]{64}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
         * The launcher may send the value in the JSON body
         * or as an Idempotency-Key header.
         */
        $sessionId = $this->input('download_session_id') ?: $this->header('Idempotency-Key');

        $this->merge([
            'download_session_id' => is_string($sessionId) ? strtolower(trim($sessionId)) : null,
            'sha256' => is_string($this->input('sha256'))
                ? strtolower(trim((string) $this->input('sha256')))
                : null,
        ]);
    }
}
