<?php

namespace App\Http\Requests\Admin\Project;

use App\Application\Project\Listings\ProjectListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectRequest extends FormRequest
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

            'progress_from' => ['nullable', 'integer', 'min:0', 'max:100'],
            'progress_to' => ['nullable', 'integer', 'min:0', 'max:100'],

            'released_at_from' => ['nullable', 'date'],
            'released_at_to' => ['nullable', 'date'],
            'last_updated_at_from' => ['nullable', 'date'],
            'last_updated_at_to' => ['nullable', 'date'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date'],

            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'min:1'],

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
            'released_at_from' => $this->normalizeDateToGregorian($this->input('released_at_from')),
            'released_at_to' => $this->normalizeDateToGregorian($this->input('released_at_to')),
            'last_updated_at_from' => $this->normalizeDateToGregorian($this->input('last_updated_at_from')),
            'last_updated_at_to' => $this->normalizeDateToGregorian($this->input('last_updated_at_to')),
            'created_at_from' => $this->normalizeDateToGregorian($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDateToGregorian($this->input('created_at_to')),
            'tag_ids' => $this->normalizeTagIds($this->input('tag_ids')),
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(ProjectListing::class);

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
    protected function normalizeTagIds(mixed $value): array
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
