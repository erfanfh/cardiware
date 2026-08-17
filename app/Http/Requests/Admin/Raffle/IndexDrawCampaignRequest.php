<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Application\Raffle\Listings\DrawCampaignListing;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDrawCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'id' => ['nullable','integer','min:1'], 'title' => ['nullable','string','max:255'], 'slug' => ['nullable','string','max:255'],
            'status' => ['nullable', Rule::enum(DrawCampaignStatusEnum::class)],
            'starts_at_from' => ['nullable','date'], 'starts_at_to' => ['nullable','date','after_or_equal:starts_at_from'],
            'closes_at_from' => ['nullable','date'], 'closes_at_to' => ['nullable','date','after_or_equal:closes_at_from'],
            'created_at_from' => ['nullable','date'], 'created_at_to' => ['nullable','date','after_or_equal:created_at_from'],
            'sort' => ['nullable', Rule::in(array_keys(app(DrawCampaignListing::class)->sortable()))],
            'direction' => ['nullable', Rule::in(['asc','desc'])],
        ];
    }
    protected function prepareForValidation(): void
    {
        foreach (['starts_at_from','starts_at_to','closes_at_from','closes_at_to','created_at_from','created_at_to'] as $key) {
            if ($this->filled($key)) $this->merge([$key => gregorian_date($this->input($key))]);
        }
    }
}
