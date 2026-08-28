<?php

namespace App\Http\Requests\Admin\Poll;

use App\Application\Slug\Services\SlugNormalizer;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
use App\Domain\Poll\Enums\PollStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pollId = $this->route('poll')?->getKey();

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('polls', 'slug')->ignore($pollId)],
            'question' => ['required', 'string', 'max:100000'],
            'description' => ['nullable', 'string', 'max:100000'],
            'status' => ['required', Rule::enum(PollStatusEnum::class)],
            'audience' => ['required', Rule::enum(PollAudienceEnum::class)],
            'results_visibility' => ['required', Rule::enum(PollResultsVisibilityEnum::class)],
            'allows_multiple_selections' => ['required', 'boolean'],
            'min_selections' => ['required', 'integer', 'min:1', 'max:100'],
            'max_selections' => ['required', 'integer', 'min:1', 'max:100', 'gte:min_selections'],
            'allow_vote_changes' => ['required', 'boolean'],
            'randomize_options' => ['required', 'boolean'],
            'show_response_count' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'options' => ['required', 'array', 'min:2', 'max:100'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['required', 'string', 'max:500', 'distinct'],
            'options.*.description' => ['nullable', 'string', 'max:2000'],
            'options.*.color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'options.*.is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان مدیریتی', 'slug' => 'نشانی', 'question' => 'صورت سؤال',
            'starts_at' => 'زمان شروع', 'ends_at' => 'زمان پایان',
            'options' => 'گزینه‌ها', 'options.*.label' => 'متن گزینه',
            'min_selections' => 'حداقل انتخاب', 'max_selections' => 'حداکثر انتخاب',
        ];
    }

    protected function prepareForValidation(): void
    {
        $multiple = $this->boolean('allows_multiple_selections');
        $options = collect(is_array($this->input('options')) ? $this->input('options') : [])
            ->filter(fn ($item) => is_array($item) && filled($item['label'] ?? null))
            ->map(function (array $item): array {
                $item['label'] = trim((string) ($item['label'] ?? ''));
                $item['is_active'] = filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOL);

                return $item;
            })->values()->all();

        $this->merge([
            'slug' => app(SlugNormalizer::class)->normalize(first_filled($this->input('slug'), $this->input('title'))),
            'allows_multiple_selections' => $multiple,
            'min_selections' => $multiple ? max(1, (int) $this->input('min_selections', 1)) : 1,
            'max_selections' => $multiple ? max(1, (int) $this->input('max_selections', 1)) : 1,
            'allow_vote_changes' => $this->boolean('allow_vote_changes'),
            'randomize_options' => $this->boolean('randomize_options'),
            'show_response_count' => $this->boolean('show_response_count'),
            'starts_at' => $this->normalizeDate($this->input('starts_at')),
            'ends_at' => $this->normalizeDate($this->input('ends_at')),
            'options' => $options,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $activeCount = collect($this->input('options', []))->where('is_active', true)->count();
            if ($activeCount < 2) {
                $validator->errors()->add('options', 'حداقل دو گزینه فعال لازم است.');
            }
            if ((int) $this->input('max_selections', 1) > $activeCount) {
                $validator->errors()->add('max_selections', 'حداکثر انتخاب نمی‌تواند از تعداد گزینه‌های فعال بیشتر باشد.');
            }
        });
    }

    private function normalizeDate(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? gregorian_date($value) : null;
    }
}
