<?php

namespace App\Http\Requests\Admin\Marketplace;

use App\Application\Marketplace\Listings\StoreListing;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStoreRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255'],

            'status' => ['nullable', Rule::enum(StoreStatusEnum::class)],

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
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(StoreListing::class);

        return array_keys($listing->sortable());
    }

    protected function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
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
