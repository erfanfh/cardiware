<?php

namespace App\Http\Requests\Front\Loyalty;

use Illuminate\Foundation\Http\FormRequest;

class RedeemRewardOfferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function idempotencyKey(): string
    {
        return (string)$this->validated('idempotency_key');
    }
}
