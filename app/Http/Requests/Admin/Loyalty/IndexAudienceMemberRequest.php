<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Application\Loyalty\Listings\AudienceMemberListing;
use App\Domain\Identity\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAudienceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'query' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(UserStatusEnum::class)],
            'role' => ['nullable', 'integer', 'min:1'],
            'has_account' => ['nullable', Rule::in(['0', '1'])],
            'balance_from' => ['nullable', 'integer'],
            'balance_to' => ['nullable', 'integer', 'gte:balance_from'],
            'lifetime_earned_from' => ['nullable', 'integer', 'min:0'],
            'lifetime_earned_to' => ['nullable', 'integer', 'min:0', 'gte:lifetime_earned_from'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date', 'after_or_equal:created_at_from'],
            'sort' => ['nullable', Rule::in(array_keys(app(AudienceMemberListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_at_from' => $this->normalizeDate($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDate($this->input('created_at_to')),
        ]);
    }

    private function normalizeDate(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
