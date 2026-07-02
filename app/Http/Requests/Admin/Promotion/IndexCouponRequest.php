<?php

namespace App\Http\Requests\Admin\Promotion;

use App\Application\Promotion\Listings\CouponListing;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCouponRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:255'],

            'status' => ['nullable', Rule::enum(CouponStatusEnum::class)],
            'type' => ['nullable', Rule::enum(CouponTypeEnum::class)],
            'scope' => ['nullable', Rule::enum(CouponScopeEnum::class)],

            'user_id' => ['nullable', 'integer', 'min:1'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'store_id' => ['nullable', 'integer', 'min:1'],

            'starts_at_from' => ['nullable', 'date'],
            'starts_at_to' => ['nullable', 'date'],

            'expires_at_from' => ['nullable', 'date'],
            'expires_at_to' => ['nullable', 'date'],

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
            'starts_at_from' => $this->normalizeDateToGregorian($this->input('starts_at_from')),
            'starts_at_to' => $this->normalizeDateToGregorian($this->input('starts_at_to')),

            'expires_at_from' => $this->normalizeDateToGregorian($this->input('expires_at_from')),
            'expires_at_to' => $this->normalizeDateToGregorian($this->input('expires_at_to')),

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
        $listing = app(CouponListing::class);

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
