<?php

namespace App\Http\Requests\Admin\Project;

use App\Domain\Project\Enums\ProjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            'status' => ['required', Rule::enum(ProjectStatusEnum::class)],
            'cover_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'released_at' => ['nullable', 'string'],
            'last_updated_at' => ['nullable', 'string'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'current_version' => ['nullable', 'string', 'max:255'],
            'creator_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.existing' => ['nullable', 'array'],
            'tags.existing.*' => ['integer', 'exists:taxonomy_terms,id'],
            'tags.new' => ['nullable', 'array'],
            'tags.new.*' => ['string', 'max:255'],

            'features' => ['nullable', 'array'],
            'features.*.id' => ['nullable', 'integer'],
            'features.*.title' => ['nullable', 'string', 'max:255'],
            'features.*.subtitle' => ['nullable', 'string', 'max:255'],
            'features.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'roadmap_items' => ['nullable', 'array'],
            'roadmap_items.*.id' => ['nullable', 'integer'],
            'roadmap_items.*.title' => ['required', 'string', 'max:255'],
            'roadmap_items.*.status' => ['required', 'string'],
            'roadmap_items.*.progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'roadmap_items.*.excerpt' => ['nullable', 'string'],
            'roadmap_items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'changelogs' => ['nullable', 'array'],
            'changelogs.*.id' => ['nullable', 'integer'],
            'changelogs.*.tags' => ['nullable', 'string'],
            'changelogs.*.title' => ['nullable', 'string', 'max:255'],
            'changelogs.*.body' => ['nullable', 'string'],
            'changelogs.*.published_at' => ['nullable', 'string', 'max:20'],
            'changelogs.*.sort_order' => ['nullable', 'integer', 'min:0'],
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
            'slug.unique' => 'این اسلاگ قبلاً ثبت شده است.',
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',

            'progress_percent.integer' => 'درصد پیشرفت باید عدد صحیح باشد.',
            'progress_percent.min' => 'درصد پیشرفت نمی‌تواند کمتر از :min باشد.',
            'progress_percent.max' => 'درصد پیشرفت نمی‌تواند بیشتر از :max باشد.',

            'roadmap.array' => 'ساختار نقشه راه معتبر نیست.',
            'changelogs.array' => 'ساختار آپدیت‌ها معتبر نیست.',
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
            'cover_id' => $this->input('cover.id'),
            'progress_percent' => (int)$this->input('progress_percent'),
            'roadmap' => $this->normalizeArray($this->input('roadmap')),
            'changelogs' => $this->normalizeArray($this->input('changelogs')),
            'released_at' => $this->input('released_at') ? gregorian_date($this->input('released_at')) : null,
            'last_updated_at' => $this->input('last_updated_at') ? gregorian_date($this->input('last_updated_at')) : null,
            'tags' => [
                'existing' => $existing,
                'new' => $new,
            ],
        ]);
    }

    /**
     * @return int|string|null
     */
    protected function getProjectId(): int|string|null
    {
        $project = $this->route('project');

        if (is_object($project) && isset($project->id)) {
            return $project->id;
        }

        return $project;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function normalizeArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
