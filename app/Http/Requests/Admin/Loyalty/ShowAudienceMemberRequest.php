<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Application\Loyalty\Listings\PointLedgerEntryListing;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowAudienceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::enum(PointEntryTypeEnum::class)],
            'entry_direction' => ['nullable', Rule::enum(PointEntryDirectionEnum::class)],
            'reason' => ['nullable', 'string', 'max:80'],
            'amount_from' => ['nullable', 'integer', 'min:1'],
            'amount_to' => ['nullable', 'integer', 'min:1', 'gte:amount_from'],
            'occurred_at_from' => ['nullable', 'date'],
            'occurred_at_to' => ['nullable', 'date', 'after_or_equal:occurred_at_from'],
            'sort' => ['nullable', Rule::in(array_keys(app(PointLedgerEntryListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'occurred_at_from' => $this->normalizeDateTime($this->input('occurred_at_from')),
            'occurred_at_to' => $this->normalizeDateTime($this->input('occurred_at_to')),
        ]);
    }

    private function normalizeDateTime(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
