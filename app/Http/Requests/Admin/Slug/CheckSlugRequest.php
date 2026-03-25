<?php

namespace App\Http\Requests\Admin\Slug;

use Illuminate\Foundation\Http\FormRequest;

class CheckSlugRequest extends FormRequest
{
    /**
     * Determine whether the current user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for the incoming request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'scope' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:600'],
            'ignore' => ['nullable', 'integer'],
            'ignore_type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
