<?php

namespace App\Http\Requests\Admin\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $shortText = ['required', 'string', 'max:200'];
        $description = ['nullable', 'string', 'max:2000'];

        return [
            'hero_badge' => ['nullable', 'string', 'max:100'],
            'hero_title' => $shortText,
            'hero_description' => ['required', 'string', 'max:2000'],
            'hero_primary_button_title' => ['required', 'string', 'max:100'],
            'hero_secondary_button_title' => ['required', 'string', 'max:100'],

            'stats_members_title' => ['required', 'string', 'max:100'],
            'stats_since_title' => ['required', 'string', 'max:100'],
            'stats_since_year' => ['required', 'integer', 'between:1300,1600'],
            'stats_products_title' => ['required', 'string', 'max:100'],
            'stats_content_title' => ['required', 'string', 'max:100'],

            'highlights_title' => $shortText,
            'highlights_description' => $description,
            'highlights_items' => ['nullable', 'array', 'max:6'],
            'highlights_items.*.title' => $shortText,
            'highlights_items.*.description' => $description,
            'highlights_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'members_title' => $shortText,
            'members_description' => $description,
            'members_empty_title' => $shortText,
            'member_ids' => ['nullable', 'array', 'max:24'],
            'member_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('team_members', 'id')->whereNull('deleted_at'),
            ],

            'process_title' => $shortText,
            'process_description' => $description,
            'process_items' => ['nullable', 'array', 'max:8'],
            'process_items.*.title' => $shortText,
            'process_items.*.description' => $description,
            'process_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'cta_badge' => ['nullable', 'string', 'max:100'],
            'cta_title' => $shortText,
            'cta_description' => ['required', 'string', 'max:2000'],
            'cta_primary_button_title' => ['required', 'string', 'max:100'],
            'cta_primary_button_url' => $this->urlRules(),
            'cta_secondary_button_title' => ['nullable', 'string', 'max:100'],
            'cta_secondary_button_url' => $this->urlRules(),
        ];
    }

    /** @return array<int, string> */
    private function urlRules(): array
    {
        return [
            'nullable',
            'string',
            'max:2048',
            'regex:/^(https?:\/\/|\/(?!\/)|mailto:)/i',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'hero_title' => 'عنوان اصلی',
            'stats_since_year' => 'سال شروع فعالیت',
            'highlights_items.*.title' => 'عنوان ویژگی',
            'member_ids' => 'اعضای منتخب',
            'member_ids.*' => 'عضو منتخب',
            'process_items.*.title' => 'عنوان مرحله',
            'cta_primary_button_url' => 'لینک دکمه اصلی',
            'cta_secondary_button_url' => 'لینک دکمه دوم',
        ];
    }

    protected function prepareForValidation(): void
    {
        $memberIds = collect($this->input('member_ids', []))
            ->filter(fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $this->merge(['member_ids' => $memberIds]);
    }
}
