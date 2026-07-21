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
        ]);
    }
}
