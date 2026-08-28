<?php

namespace App\Http\Requests\Admin\Poll;

use App\Application\Poll\Listings\PollListing;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', Rule::enum(PollStatusEnum::class)],
            'audience' => ['nullable', Rule::enum(PollAudienceEnum::class)],
            'results_visibility' => ['nullable', Rule::enum(PollResultsVisibilityEnum::class)],
            'timing' => ['nullable', Rule::in(['not_started', 'accepting_votes', 'ended', 'without_end'])],
            'starts_at_from' => ['nullable', 'date'],
            'starts_at_to' => ['nullable', 'date'],
            'ends_at_from' => ['nullable', 'date'],
            'ends_at_to' => ['nullable', 'date'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date'],
            'sort' => ['nullable', Rule::in(array_keys(app(PollListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
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

    private function normalizeDate(mixed $value): ?string
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

        return strtolower(trim($value)) === 'asc' ? 'asc' : 'desc';
    }
}
