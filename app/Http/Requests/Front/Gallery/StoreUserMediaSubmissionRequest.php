<?php

namespace App\Http\Requests\Front\Gallery;

use App\Domain\Content\Enums\UserMediaSubmissionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserMediaSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(UserMediaSubmissionTypeEnum::class)],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'image_url' => [
                'required_if:type,image',
                'nullable',
                'url',
                'max:600',
            ],
            'video_url' => [
                'required_if:type,video',
                'nullable',
                'url',
                'max:600',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع رسانه را انتخاب کنید.',
            'title.required' => 'عنوان را وارد کنید.',
            'title.min' => 'عنوان باید حداقل ۳ نویسه باشد.',
            'image_url.required_if' => 'لینک تصویر را وارد کنید.',
            'image_url.url' => 'لینک تصویر معتبر نیست.',
            'video_url.required_if' => 'لینک ویدیو (آپارات یا یوتیوب) را وارد کنید.',
            'video_url.url' => 'لینک ویدیو معتبر نیست.',
        ];
    }
}
