<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionDrawCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                DrawCampaignStatusEnum::SCHEDULED->value,
                DrawCampaignStatusEnum::OPEN->value,
                DrawCampaignStatusEnum::CLOSED->value,
                DrawCampaignStatusEnum::CANCELLED->value,
            ])],
            'reason' => ['nullable','string','max:1000', Rule::requiredIf(fn() => $this->input('status') === DrawCampaignStatusEnum::CANCELLED->value)],
        ];
    }
}
