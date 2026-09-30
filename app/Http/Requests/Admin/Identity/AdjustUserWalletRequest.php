<?php

namespace App\Http\Requests\Admin\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustUserWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('wallet.adjust') ?? false;
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'withdrawable' => ['required', 'boolean'],
            'note' => ['required', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'direction' => 'نوع تغییر',
            'amount' => 'مبلغ',
            'withdrawable' => 'وضعیت برداشت',
            'note' => 'دلیل تغییر',
            'idempotency_key' => 'شناسه عملیات',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('note'))) {
            $this->merge(['note' => trim($this->input('note'))]);
        }
    }
}
