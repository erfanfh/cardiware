<?php

namespace App\Http\Requests\Admin\Identity;

use App\Application\Identity\Listings\UserListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
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
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],

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
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(UserListing::class);

        return array_keys($listing->sortable());
    }

    protected function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
