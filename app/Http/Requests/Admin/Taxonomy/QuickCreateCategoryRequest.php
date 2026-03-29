<?php

namespace App\Http\Requests\Admin\Taxonomy;

use Illuminate\Foundation\Http\FormRequest;

class QuickCreateCategoryRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer'],
        ];
    }
}
