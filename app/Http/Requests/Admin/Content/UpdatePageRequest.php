<?php

namespace App\Http\Requests\Admin\Content;

use App\Application\Content\Services\PageTemplateService;
use App\Domain\Content\Enums\PostStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $templates = app(PageTemplateService::class)->all();

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'status' => ['required', Rule::enum(PostStatusEnum::class)],
            'template' => ['nullable', 'string', 'in:' . implode(',', array_keys($templates))],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'published_at' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان',
            'slug' => 'اسلاگ',
            'status' => 'وضعیت',
            'released_at' => 'تاریخ انتشار',
            'last_updated_at' => 'تاریخ آخرین بروزرسانی',
            'excerpt' => 'خلاصه',
            'body' => 'توضیحات',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'picture_id' => $this->input('picture.id'),
            'author_id' => auth()->user()->id,
            'published_at' => $this->input('published_at') ? gregorian_date($this->input('published_at')) : date('Y-m-d H:i:s'),
        ]);
    }
}
