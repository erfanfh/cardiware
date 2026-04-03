<?php

namespace App\Http\Requests\Admin\Content;

use App\Application\Content\Listings\PostListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],

            'published_at_from' => ['nullable', 'date'],
            'published_at_to' => ['nullable', 'date'],

            'author_ids' => ['nullable', 'array'],
            'author_ids.*' => ['integer', 'min:1'],

            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'min:1'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'min:1'],

            'sort' => [
                'nullable',
                'string',
                Rule::in($this->allowedSortKeys()),
            ],
            'direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'published_at_from' => $this->normalizeDateToGregorian($this->input('published_at_from')),
            'published_at_to' => $this->normalizeDateToGregorian($this->input('published_at_to')),
            'tag_ids' => $this->normalizeTaxonomyIds($this->input('tag_ids')),
            'category_ids' => $this->normalizeTaxonomyIds($this->input('category_ids')),
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(PostListing::class);

        return array_keys($listing->sortable());
    }

    protected function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }

    /**
     * @param mixed $value
     * @return array<int, int>
     */
    protected function normalizeTaxonomyIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(static fn($id) => filled($id))
            ->map(static fn($id) => (int)$id)
            ->filter(static fn(int $id) => $id > 0)
            ->values()
            ->all();
    }

    protected function normalizeDirection(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['asc', 'desc'], true) ? $value : 'asc';
    }
}
