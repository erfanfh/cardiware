<?php

namespace App\Http\Requests\Api\V1\Library;

use Illuminate\Foundation\Http\FormRequest;

class LibraryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }

    public function perPage(): int
    {
        return (int)$this->validated('per_page', 20);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'The number of library items returned per page. Maximum: 50.',
                'example' => 20,
            ],
        ];
    }
}
