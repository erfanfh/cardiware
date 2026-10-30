<?php

namespace App\Http\Requests\Admin\Story;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('story') ? 'story.edit' : 'story.create');
    }

    public function rules(): array
    {
        $image = fn () => Rule::exists('media_files', 'id')->where('type', 'image')
            ->where('visibility', 'public')->where('status', 'ready')->whereNotNull('primary_variant_id');

        return [
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'image.id' => ['required', 'integer', $image()],
            'thumbnail.id' => ['nullable', 'integer', $image()],
            'link_url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'link_label' => ['nullable', 'string', 'max:100'],
            'duration_seconds' => ['required', 'integer', 'between:3,60'],
            'sort_order' => ['required', 'integer', 'between:0,1000000'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i', Rule::when($this->filled('starts_at'), 'after:starts_at')],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان', 'subtitle' => 'زیرعنوان', 'caption' => 'توضیحات',
            'image.id' => 'تصویر اصلی', 'thumbnail.id' => 'کاور', 'link_url' => 'لینک',
            'link_label' => 'متن دکمه', 'duration_seconds' => 'مدت نمایش',
            'sort_order' => 'ترتیب نمایش', 'starts_at' => 'زمان شروع', 'ends_at' => 'زمان پایان',
        ];
    }
}
