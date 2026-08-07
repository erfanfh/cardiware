<?php

namespace App\Http\Requests\Api\V1\Launcher;

use Illuminate\Foundation\Http\FormRequest;

class LauncherCatalogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function perPage(): int
    {
        return (int)$this->validated('per_page', 20);
    }
}
