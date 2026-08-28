<?php

namespace App\Http\Requests\Admin\Video;

use App\Application\Video\Listings\VideoListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],
            'source_type' => ['nullable', 'string', 'max:100'],
            'published_at_from' => ['nullable', 'date'],
            'published_at_to' => ['nullable', 'date'],
            'author_ids' => ['nullable', 'array'],
            'author_ids.*' => ['integer', 'min:1'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'min:1'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'min:1'],
            'sort' => ['nullable', 'string', Rule::in(array_keys(app(VideoListing::class)->sortable()))],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'published_at_from' => $this->normalizeDate($this->input('published_at_from')),
            'published_at_to' => $this->normalizeDate($this->input('published_at_to')),
            'author_ids' => $this->normalizeIds($this->input('author_ids')),
            'tag_ids' => $this->normalizeIds($this->input('tag_ids')),
            'category_ids' => $this->normalizeIds($this->input('category_ids')),
            'direction' => in_array($this->input('direction'), ['asc', 'desc'], true) ? $this->input('direction') : null,
        ]);
    }

    private function normalizeDate(mixed $value): mixed
    {
        return is_string($value) && trim($value) !== '' ? gregorian_date($value) : null;
    }

    private function normalizeIds(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
