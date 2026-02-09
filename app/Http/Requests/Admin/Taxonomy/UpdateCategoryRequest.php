<?php

namespace App\Http\Requests\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:taxonomy_terms,id', 'not_in:' . $categoryId,],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', Rule::enum(TaxonomyTermTypeEnum::class)],
        ];

        if ($this->route('term')) {
            $rules['slug_change_confirmed'] = 'sometimes|in:0,1';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'این اسلاگ قبلاً استفاده شده است.',
            'parent_id.not_in' => 'نمی‌توانید دسته را زیرمجموعه خودش قرار دهید.',
        ];
    }
}
