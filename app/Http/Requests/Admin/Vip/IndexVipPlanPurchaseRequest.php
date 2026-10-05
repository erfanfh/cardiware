<?php

namespace App\Http\Requests\Admin\Vip;

use App\Application\Vip\Listings\VipPlanPurchaseListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVipPlanPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user' => ['nullable', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'scheduled', 'expired', 'revoked'])],
            'sort' => ['nullable', 'string', Rule::in(array_keys(app(VipPlanPurchaseListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];

        foreach (['starts_at', 'expires_at', 'created_at'] as $field) {
            $rules[$field.'_from'] = ['nullable', 'date'];
            $rules[$field.'_to'] = ['nullable', 'date'];

            if ($this->filled($field.'_from')) {
                $rules[$field.'_to'][] = 'after_or_equal:'.$field.'_from';
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $dates = [];

        foreach (['starts_at', 'expires_at', 'created_at'] as $field) {
            foreach (['from', 'to'] as $bound) {
                $key = $field.'_'.$bound;
                $value = $this->input($key);
                $dates[$key] = $this->normalizeDate($value);
            }
        }

        $this->merge($dates);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $value = trim($value);

            return gregorian_date($value, 'Y-m-d', str_contains($value, ':') ? 'Y/m/d H:i' : 'Y/m/d');
        } catch (\Throwable) {
            return 'invalid-date';
        }
    }
}
