<?php

namespace App\Http\Requests\Front\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class AccountDrawCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign' => ['nullable', 'string', 'max:40'],
        ];
    }
}
