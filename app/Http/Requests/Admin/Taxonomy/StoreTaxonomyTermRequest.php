<?php

namespace App\Http\Requests\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Models\TaxonomyTermSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxonomyTermRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255',
                //check duplicate slug at both tables
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }

                    $existsInTerms = TaxonomyTerm::where('slug', $value)->exists();

                    $existsInSlugs = TaxonomyTermSlug::where('route_key', $value)
                        ->where('type', $this->input('type'))
                        ->exists();

                    if ($existsInTerms || $existsInSlugs) {
                        $fail('این اسلاگ قبلاً استفاده شده است.');
                    }
                },],
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
