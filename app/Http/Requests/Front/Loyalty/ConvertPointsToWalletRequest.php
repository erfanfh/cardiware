<?php

namespace App\Http\Requests\Front\Loyalty;

use Illuminate\Foundation\Http\FormRequest;

class ConvertPointsToWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'points' => 'مقدار امتیاز',
            'idempotency_key' => 'شناسه درخواست',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('account.loyalty').'#convert-points';
    }
}
