<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;

class SaveVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable'],
            'slug' => ['nullable'],
            'excerpt' => ['nullable'],
            'body' => ['nullable'],
            'status' => ['nullable'],
            'source_type' => ['nullable'],
            'video_url' => ['nullable'],
            'cover_media_file_id' => ['nullable'],
            'duration_seconds' => ['nullable'],
            'is_featured' => ['nullable'],
            'comments_enabled' => ['nullable'],
            'guest_comments_enabled' => ['nullable'],
            'published_at' => ['nullable'],
            'author_id' => ['nullable'],
            'display_views_offset' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'category_ids' => ['nullable'],
            'tags' => ['nullable'],
            'seo' => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'display_views_offset' => 'افزایش نمایشی بازدید',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tags = is_array($this->input('tags')) ? $this->input('tags') : [];
        $duration = $this->input('duration_seconds');
        $coverId = data_get($this->input('cover'), 'id');
        $publishedAt = $this->input('published_at');

        $this->merge([
            'title' => $this->scalarString('title'),
            'slug' => $this->scalarString('slug'),
            'excerpt' => $this->scalarString('excerpt'),
            'body' => $this->scalarString('body'),
            'video_url' => $this->scalarString('video_url'),
            'status' => in_array($this->input('status'), ['active', 'inactive'], true) ? $this->input('status') : 'inactive',
            'source_type' => in_array($this->input('source_type'), ['aparat', 'direct'], true) ? $this->input('source_type') : 'aparat',
            'cover_media_file_id' => is_numeric($coverId) && (int)$coverId > 0 ? (int)$coverId : null,
            'duration_seconds' => is_numeric($duration) && (int)$duration > 0 ? (int)$duration : null,
            'author_id' => $this->route('video')?->author_id ?? auth()->id(),
            'display_views_offset' => $this->input('display_views_offset', 0),
            'published_at' => is_scalar($publishedAt) && filled($publishedAt) ? gregorian_date((string)$publishedAt) : now(),
            'is_featured' => $this->boolean('is_featured'),
            'comments_enabled' => $this->boolean('comments_enabled'),
            'guest_comments_enabled' => $this->boolean('guest_comments_enabled'),
            'category_ids' => collect(is_array($this->input('category_ids')) ? $this->input('category_ids') : [])->map(fn ($id) => (int)$id)->filter()->unique()->values()->all(),
            'tags' => [
                'existing' => collect(data_get($tags, 'existing', []))->filter()->map(fn ($id) => (int)$id)->unique()->values()->all(),
                'new' => collect(data_get($tags, 'new', []))->map(fn ($v) => trim((string)$v))->filter()->unique()->values()->all(),
            ],
            'seo' => is_array($this->input('seo')) ? $this->input('seo') : [],
        ]);
    }

    private function scalarString(string $key): string
    {
        $value = $this->input($key);

        return is_scalar($value) ? trim((string)$value) : '';
    }
}
