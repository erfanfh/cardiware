<?php

namespace App\Http\Requests\Admin\System;

use App\Infrastructure\Services\Setting\Groups\CareersSettings;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCareersSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];
        foreach (array_keys(CareersSettings::defaults()) as $key) {
            $rules[$key] = str_ends_with($key, '_items')
                ? ['present', 'array', 'max:20']
                : ['nullable', 'string', 'max:3000'];
        }

        foreach (['why_items', 'benefits_items', 'process_items', 'review_items'] as $key) {
            $rules["{$key}.*.title"] = ['required', 'string', 'max:150'];
            $rules["{$key}.*.description"] = ['nullable', 'string', 'max:1000'];
            $rules["{$key}.*.sort_order"] = ['nullable', 'integer', 'min:0'];
        }
        $rules['benefits_items.*.icon'] = ['nullable', 'string', 'max:255'];
        foreach (['skills_items', 'tips_items'] as $key) {
            $rules["{$key}.*.title"] = ['required', 'string', 'max:255'];
            $rules["{$key}.*.sort_order"] = ['nullable', 'integer', 'min:0'];
        }
        $rules['faq_items.*.title'] = ['required', 'string', 'max:255'];
        $rules['faq_items.*.answer'] = ['required', 'string', 'max:3000'];
        $rules['faq_items.*.sort_order'] = ['nullable', 'integer', 'min:0'];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        foreach (array_keys(CareersSettings::defaults()) as $key) {
            if (str_ends_with($key, '_items') && $this->input($key) === '') {
                $this->merge([$key => []]);
            }
        }
    }
}
