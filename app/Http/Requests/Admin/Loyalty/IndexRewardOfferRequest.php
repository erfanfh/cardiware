<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Application\Loyalty\Listings\RewardOfferListing;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRewardOfferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::enum(RewardTypeEnum::class)],
            'is_active' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'points_cost_from' => ['nullable', 'integer', 'min:1'],
            'points_cost_to' => ['nullable', 'integer', 'min:1'],
            'starts_at_from' => ['nullable', 'date'],
            'starts_at_to' => ['nullable', 'date'],
            'ends_at_from' => ['nullable', 'date'],
            'ends_at_to' => ['nullable', 'date'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', Rule::in(array_keys(app(RewardOfferListing::class)->sortable()))],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['starts_at_from', 'starts_at_to', 'ends_at_from', 'ends_at_to', 'created_at_from', 'created_at_to'] as $key) {
            $value = $this->input($key);
            if (is_string($value) && trim($value) !== '') {
                $this->merge([$key => gregorian_date($value)]);
            }
        }
    }
}
