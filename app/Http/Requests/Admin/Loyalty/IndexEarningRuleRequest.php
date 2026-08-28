<?php

namespace App\Http\Requests\Admin\Loyalty;

use App\Application\Loyalty\Listings\EarningRuleListing;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEarningRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::enum(EarningRuleTypeEnum::class)],
            'is_active' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'priority' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'starts_at_from' => ['nullable', 'date'],
            'starts_at_to' => ['nullable', 'date'],
            'ends_at_from' => ['nullable', 'date'],
            'ends_at_to' => ['nullable', 'date'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', Rule::in(array_keys(app(EarningRuleListing::class)->sortable()))],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'starts_at_from' => $this->normalizeDate($this->input('starts_at_from')),
            'starts_at_to' => $this->normalizeDate($this->input('starts_at_to')),
            'ends_at_from' => $this->normalizeDate($this->input('ends_at_from')),
            'ends_at_to' => $this->normalizeDate($this->input('ends_at_to')),
            'created_at_from' => $this->normalizeDate($this->input('created_at_from')),
            'created_at_to' => $this->normalizeDate($this->input('created_at_to')),
            'direction' => $this->normalizeDirection($this->input('direction')),
        ]);
    }

    private function normalizeDate(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }

    private function normalizeDirection(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['asc', 'desc'], true) ? $value : 'asc';
    }
}
