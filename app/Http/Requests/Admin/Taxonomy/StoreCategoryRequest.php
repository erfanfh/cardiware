<?php

namespace App\Http\Requests\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(TaxonomyTermTypeEnum::class)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:taxonomy_terms,id'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'این اسلاگ قبلاً برای دسته بندی دیگری استفاده شده است.',
        ];
    }
}
