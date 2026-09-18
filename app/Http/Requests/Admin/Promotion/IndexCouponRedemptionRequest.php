<?php

namespace App\Http\Requests\Admin\Promotion;

use App\Application\Promotion\Listings\CouponRedemptionListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCouponRedemptionRequest extends FormRequest
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
            'user' => ['nullable', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:255'],
            'discount_from' => ['nullable', 'integer', 'min:0'],
            'discount_to' => ['nullable', 'integer', 'min:0'],
            'redeemed_at_from' => ['nullable', 'date'],
            'redeemed_at_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', Rule::in($this->allowedSortKeys())],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'redeemed_at_from' => $this->normalizeDateToGregorian($this->input('redeemed_at_from')),
            'redeemed_at_to' => $this->normalizeDateToGregorian($this->input('redeemed_at_to')),
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedSortKeys(): array
    {
        return array_keys(app(CouponRedemptionListing::class)->sortable());
    }

    private function normalizeDateToGregorian(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }

    private function normalizeDirection(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['asc', 'desc'], true) ? $value : 'desc';
    }
}
