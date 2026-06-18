<?php

namespace App\Http\Requests\Admin\Commerce;

use App\Application\Commerce\Listings\OrderListing;
use Illuminate\Foundation\Http\FormRequest;

class IndexOrderRequest extends FormRequest
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
            'order_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],
            'user' => ['nullable', 'string', 'max:255'],

            'total_from' => ['nullable', 'string'],
            'total_to' => ['nullable', 'string'],

            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date_from' => $this->normalizeDateToGregorian($this->input('date_from')),
            'date_to' => $this->normalizeDateToGregorian($this->input('date_to')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        $listing = app(OrderListing::class);

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
