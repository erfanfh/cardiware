<?php

namespace App\Http\Requests\Front\Raffle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDrawCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['all', 'open', 'upcoming', 'finished'])],
        ];
    }
}
