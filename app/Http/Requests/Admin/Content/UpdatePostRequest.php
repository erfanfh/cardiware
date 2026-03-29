<?php

namespace App\Http\Requests\Admin\Content;

use App\Domain\Content\Enums\PostStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'status' => ['required', Rule::enum(PostStatusEnum::class)],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'body' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'published_at' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.existing' => ['nullable', 'array'],
            'tags.existing.*' => ['integer', 'exists:taxonomy_terms,id'],
            'tags.new' => ['nullable', 'array'],
            'tags.new.*' => ['string', 'max:255'],

            'category_ids' => ['nullable', 'array'],
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
            'progress_percent' => 'درصد پیشرفت',
            'current_version' => 'نسخه فعلی',
            'creator_name' => 'نام سازنده',
            'platform' => 'پلتفرم',
            'body' => 'توضیحات',
            'excerpt' => 'خلاصه',

            'roadmap' => 'نقشه راه',
            'roadmap.*.title' => 'عنوان آیتم نقشه راه',
            'roadmap.*.status' => 'وضعیت آیتم نقشه راه',
            'roadmap.*.progress_percent' => 'درصد پیشرفت آیتم نقشه راه',
            'roadmap.*.excerpt' => 'توضیح آیتم نقشه راه',
            'roadmap.*.sort_order' => 'ترتیب آیتم نقشه راه',

            'changelogs' => 'آپدیت‌ها و تغییرات',
            'changelogs.*.version' => 'نسخه آپدیت',
            'changelogs.*.title' => 'عنوان آپدیت',
            'changelogs.*.body' => 'توضیحات آپدیت',
            'changelogs.*.released_at' => 'تاریخ انتشار آپدیت',
            'changelogs.*.sort_order' => 'ترتیب آپدیت',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'اسلاگ فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags', []);

        $existing = collect(data_get($tags, 'existing', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        $new = collect(data_get($tags, 'new', []))
            ->filter(fn($title) => filled($title))
            ->map(fn($title) => preg_replace('/\s+/', ' ', trim((string)$title)))
            ->filter()
            ->unique(fn($title) => mb_strtolower($title))
            ->values()
            ->all();

        $this->merge([
            'picture_id' => $this->input('picture.id'),
            'tags' => [
                'existing' => $existing,
                'new' => $new,
            ],
            'author_id' => auth()->user()->id,
            'published_at' => $this->input('published_at') ? gregorian_date($this->input('published_at')) : date('Y-m-d H:i:s'),
        ]);
    }
}
