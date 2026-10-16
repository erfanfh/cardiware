<?php

namespace App\Http\Requests\Admin\Content;

use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Download\Enums\DownloadEntryTypeEnum;
use App\Domain\Feature\Enums\FrontBadgeOptionEnum;
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
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
            ],
            'status' => ['required', Rule::enum(PostStatusEnum::class)],
            'is_pinned' => ['boolean'],
            'picture_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'published_at' => ['nullable', 'string'],
            'display_views_offset' => ['required', 'integer', 'min:0', 'max:4294967295'],

            'tags' => ['nullable', 'array'],
            'tags.existing' => ['nullable', 'array'],
            'tags.existing.*' => ['integer', 'exists:taxonomy_terms,id'],
            'tags.new' => ['nullable', 'array'],
            'tags.new.*' => ['string', 'max:255'],

            'category_ids' => ['nullable', 'array'],

            'front_badge' => ['nullable', Rule::enum(FrontBadgeOptionEnum::class)],
            'is_special' => ['nullable', 'boolean'],
            'is_vip' => ['nullable', 'boolean'],
            'is_updated' => ['nullable', 'boolean'],

            'download_sections' => ['nullable', 'array'],
            'download_sections.*' => ['array'],

            'download_sections.*.id' => ['nullable', 'integer'],
            'download_sections.*.title' => ['nullable', 'string', 'max:255'],
            'download_sections.*.password' => ['nullable', 'string', 'max:255'],
            'download_sections.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'download_sections.*.entries' => ['nullable', 'array'],
            'download_sections.*.entries.*' => ['array'],

            'download_sections.*.entries.*.id' => ['nullable', 'integer'],
            'download_sections.*.entries.*.type' => ['nullable', Rule::enum(DownloadEntryTypeEnum::class)],
            'download_sections.*.entries.*.title' => ['nullable', 'string', 'max:255'],
            'download_sections.*.entries.*.content' => ['nullable', 'string'],
            'download_sections.*.entries.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'image_gallery_items' => ['nullable', 'array'],
            'image_gallery_items.*' => ['array'],

            'image_gallery_items.*.id' => ['nullable', 'integer'],
            'image_gallery_items.*.title' => ['nullable', 'string', 'max:255'],
            'image_gallery_items.*.image.id' => ['nullable', 'exists:media_files,id'],
            'image_gallery_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'video_gallery_items' => ['nullable', 'array'],
            'video_gallery_items.*' => ['array'],
            'video_gallery_items.*.id' => ['nullable', 'integer'],
            'video_gallery_items.*.title' => ['nullable', 'string', 'max:255'],
            'video_gallery_items.*.video_url' => ['nullable', 'url', 'max:2048'],
            'video_gallery_items.*.cover' => ['nullable', 'array'],
            'video_gallery_items.*.cover.id' => ['nullable', 'integer', 'exists:media_files,id'],
            'video_gallery_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'seo' => ['nullable', 'array'],

            'information_boxes' => ['nullable', 'array'],
            'information_boxes.*' => ['array'],
            'information_boxes.prerequisites.body' => ['nullable', 'string'],
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
            'body' => 'توضیحات',
            'excerpt' => 'خلاصه',
            'display_views_offset' => 'افزایش نمایشی بازدید',
            'information_boxes.prerequisites.body' => 'پیش نیاز ها',
            'video_gallery_items.*.title' => 'عنوان ویدیو',
            'video_gallery_items.*.video_url' => 'لینک ویدیو',
            'video_gallery_items.*.cover.id' => 'کاور ویدیو',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'download_sections.array' => 'ساختار سکشن‌های دانلود نامعتبر است.',
            'download_sections.*.title.max' => 'عنوان سکشن دانلود نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'download_sections.*.password.max' => 'رمز فایل نمی‌تواند بیشتر از 255 کاراکتر باشد.',

            'download_sections.*.entries.array' => 'ساختار آیتم‌های سکشن دانلود نامعتبر است.',
            'download_sections.*.entries.*.type.enum' => 'نوع آیتم دانلود نامعتبر است.',
            'download_sections.*.entries.*.title.max' => 'عنوان آیتم دانلود نمی‌تواند بیشتر از 255 کاراکتر باشد.',

            'video_gallery_items.*.video_url.url' => 'لینک ویدیو باید یک آدرس معتبر باشد.',
            'video_gallery_items.*.cover.id.exists' => 'کاور انتخاب‌شده معتبر نیست.',
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
            'author_id' => $this->route('post')?->author_id ?? auth()->id(),
            'published_at' => $this->input('published_at') ? gregorian_date($this->input('published_at')) : date('Y-m-d H:i:s'),
            'display_views_offset' => $this->input('display_views_offset', 0),
        ]);
    }
}
