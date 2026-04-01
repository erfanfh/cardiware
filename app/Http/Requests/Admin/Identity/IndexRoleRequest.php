<?php

namespace App\Http\Requests\Admin\Identity;

use App\Application\Identity\Listings\RoleListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRoleRequest extends FormRequest
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
            'title' => ['nullable', 'string'],

            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'min:1'],

            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date'],

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
            'created_at_from' => $this->normalizeDateToGregorian($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDateToGregorian($this->input('created_at_to')),
            'permission_ids' => $this->normalizepermissionIds($this->input('permission_ids')),
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(RoleListing::class);

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
    protected function normalizepermissionIds(mixed $value): array
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
