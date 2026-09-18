<?php

namespace App\Http\Requests\Admin\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAboutUsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'hero_badge' => ['nullable', 'string', 'max:150'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['required', 'string', 'max:20000'],
            'hero_primary_button_title' => ['required', 'string', 'max:100'],
            'hero_secondary_button_title' => ['required', 'string', 'max:100'],
            'hero_image' => ['nullable', 'array'],
            'hero_image.id' => ['nullable', 'integer', Rule::exists('media_files', 'id')],
            'hero_image.path' => ['nullable', 'string', 'max:2048'],

            'stats_in_progress_title' => ['required', 'string', 'max:100'],
            'stats_completed_title' => ['required', 'string', 'max:100'],
            'stats_products_title' => ['required', 'string', 'max:100'],
            'stats_users_title' => ['required', 'string', 'max:100'],

            'our_mission_box_title' => ['required', 'string', 'max:150'],
            'our_mission_description' => ['required', 'string', 'max:20000'],

            'what_we_build_box_title' => ['required', 'string', 'max:150'],
            'what_we_build_items' => ['nullable', 'array', 'max:12'],
            'what_we_build_items.*.title' => ['required', 'string', 'max:150'],
            'what_we_build_items.*.subtitle' => ['nullable', 'string', 'max:500'],
            'what_we_build_items.*.url' => [
                'nullable',
                'string',
                'max:2048',
                'regex:/^(https?:\/\/|\/(?!\/))/i',
            ],
            'what_we_build_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'projects_box_title' => ['required', 'string', 'max:150'],
            'projects_box_description' => ['nullable', 'string', 'max:500'],
            'projects_button_title' => ['required', 'string', 'max:100'],
            'projects_count' => ['required', 'integer', 'between:1,6'],

            'our_team_box_title' => ['required', 'string', 'max:150'],
            'our_team_description' => ['nullable', 'string', 'max:500'],
            'team_members_count' => ['required', 'integer', 'between:1,12'],

            'faqs_box_title' => ['required', 'string', 'max:150'],
            'faqs_description' => ['nullable', 'string', 'max:500'],
            'faqs_items' => ['nullable', 'array', 'max:30'],
            'faqs_items.*.title' => ['required', 'string', 'max:255'],
            'faqs_items.*.answer' => ['required', 'string', 'max:5000'],
            'faqs_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'contact_us_box_title' => ['required', 'string', 'max:200'],
            'contact_us_description' => ['required', 'string', 'max:1000'],
            'contact_us_button_title' => ['required', 'string', 'max:100'],
            'contact_box_title' => ['required', 'string', 'max:150'],
            'contact_telegram' => ['nullable', 'string', 'max:2048'],
            'contact_instagram' => ['nullable', 'string', 'max:2048'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_hour' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'hero_title' => 'عنوان اصلی',
            'hero_description' => 'توضیحات Hero',
            'hero_image.id' => 'تصویر Hero',
            'what_we_build_items.*.title' => 'عنوان حوزه فعالیت',
            'what_we_build_items.*.url' => 'لینک حوزه فعالیت',
            'projects_count' => 'تعداد پروژه‌های نمایشی',
            'team_members_count' => 'تعداد اعضای تیم',
            'faqs_items.*.title' => 'عنوان سوال',
            'faqs_items.*.answer' => 'پاسخ سوال',
            'contact_email' => 'ایمیل',
        ];
    }
}
