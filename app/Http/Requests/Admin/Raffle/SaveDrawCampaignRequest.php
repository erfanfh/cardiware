<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Application\Slug\Services\SlugNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class SaveDrawCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'slug' => ['required','string','max:255','alpha_dash'],
            'short_description' => ['nullable','string','max:1000'],
            'body' => ['nullable','string'],
            'rules' => ['nullable','string'],
            'cover_media_file_id' => ['nullable','integer','exists:media_files,id'],
            'show_winners_publicly' => ['required','boolean'],

            'starts_at' => ['nullable','date'],
            'closes_at' => ['nullable','date','after:starts_at'],
            'draw_at' => ['nullable','date'],
            'ticket_limit' => ['nullable','integer','min:1'],
            'ticket_limit_per_user' => ['nullable','integer','min:1'],
            'points_per_ticket' => ['sometimes', 'required', 'integer', 'min:1', 'max:4294967295'],
            'ticket_price' => ['nullable', 'integer', 'min:0'],
            'allow_multiple_wins_per_user' => ['required','boolean'],
            'reserve_winners_per_prize' => ['required','integer','min:0','max:100'],
            'winner_confirmation_window_hours' => ['nullable','integer','min:1','max:8760'],

            'prizes' => ['nullable','array'],
            'prizes.*.id' => ['nullable','integer'],
            'prizes.*.title' => ['nullable','string','max:255'],
            'prizes.*.description' => ['nullable','string','max:2000'],
            'prizes.*.delivery_description' => ['nullable','string','max:2000'],
            'prizes.*.estimated_value' => ['nullable','integer','min:0'],
            'prizes.*.media.id' => ['nullable','integer','exists:media_files,id'],
            'prizes.*.quantity' => ['nullable','integer','min:1','max:10000'],
            'prizes.*.reserve_winners_count' => ['nullable','integer','min:0','max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان', 'slug' => 'اسلاگ', 'starts_at' => 'زمان شروع', 'closes_at' => 'زمان پایان',
            'draw_at' => 'زمان قرعه کشی', 'ticket_limit' => 'ظرفیت کل بلیط', 'ticket_limit_per_user' => 'سقف بلیط هر کاربر',
            'prizes.*.title' => 'عنوان جایزه', 'prizes.*.quantity' => 'تعداد جایزه',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => app(SlugNormalizer::class)->normalize(
                first_filled($this->input('slug'), $this->input('title')),
            ),
            'cover_media_file_id' => $this->input('cover.id'),
            'show_winners_publicly' => $this->boolean('show_winners_publicly'),
            'allow_multiple_wins_per_user' => $this->boolean('allow_multiple_wins_per_user'),
            'starts_at' => $this->normalizeDate($this->input('starts_at')),
            'closes_at' => $this->normalizeDate($this->input('closes_at')),
            'draw_at' => $this->normalizeDate($this->input('draw_at')),
            'ticket_limit' => $this->nullableInt($this->input('ticket_limit')),
            'ticket_limit_per_user' => $this->nullableInt($this->input('ticket_limit_per_user')),
            'winner_confirmation_window_hours' => $this->nullableInt($this->input('winner_confirmation_window_hours')),
            'prizes' => is_array($this->input('prizes')) ? $this->input('prizes') : [],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $total = $this->input('ticket_limit');
            $perUser = $this->input('ticket_limit_per_user');
            if ($total !== null && $perUser !== null && (int)$perUser > (int)$total) {
                $validator->errors()->add('ticket_limit_per_user', 'سقف بلیط هر کاربر نمی‌تواند بیشتر از ظرفیت کل باشد.');
            }

            $drawAt = $this->input('draw_at');
            $closesAt = $this->input('closes_at');
            if ($drawAt && $closesAt && strtotime($drawAt) < strtotime($closesAt)) {
                $validator->errors()->add('draw_at', 'زمان قرعه کشی نمی‌تواند قبل از زمان پایان شرکت باشد.');
            }
        });
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') return null;
        return gregorian_date($value);
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }
}
