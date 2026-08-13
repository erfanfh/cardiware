<?php

namespace App\Http\Requests\Admin\Commerce;

use App\Application\Commerce\Listings\AbandonedCartListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAbandonedCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', Rule::in(['user', 'guest'])],
            'total_from' => ['nullable', 'integer', 'min:0'],
            'total_to' => ['nullable', 'integer', 'min:0'],
            'updated_from' => ['nullable', 'date'],
            'updated_to' => ['nullable', 'date'],
            'sort' => ['nullable', Rule::in($this->allowedSortKeys())],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'updated_from' => $this->normalizeDateToGregorian($this->input('updated_from')),
            'updated_to' => $this->normalizeDateToGregorian($this->input('updated_to')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedSortKeys(): array
    {
        return array_keys(app(AbandonedCartListing::class)->sortable());
    }

    private function normalizeDateToGregorian(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
