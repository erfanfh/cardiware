<?php

namespace App\Http\Requests\Front\Financial;

use App\Rules\IranianIban;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletWithdrawalRequest extends FormRequest
{
    protected $errorBag = 'walletWithdrawal';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required', 'integer',
                'min:' . config('financial.wallet.withdrawal.minimum_amount', 50000),
                'max:' . config('financial.wallet.withdrawal.maximum_amount', 20000000),
            ],
            'iban' => ['required', new IranianIban()],
            'card_number' => ['required', 'regex:/^[0-9]{16}$/'],
            'account_holder' => ['required', 'string', 'max:150'],
            'user_note' => ['nullable', 'string', 'max:1000'],
            'request_key' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'مبلغ برداشت',
            'iban' => 'شماره شبا',
            'card_number' => 'شماره کارت',
            'account_holder' => 'نام صاحب حساب',
            'user_note' => 'توضیحات',
            'request_key' => 'شناسه درخواست',
        ];
    }

    public function messages(): array
    {
        return ['card_number.regex' => 'شماره کارت باید ۱۶ رقم باشد.'];
    }

    protected function prepareForValidation(): void
    {
        $amount = $this->input('amount');
        if (is_string($amount)) {
            $this->merge(['amount' => preg_replace('/[,\x{066C}\x{060C}\s]+/u', '', $this->normalizeDigits($amount))]);
        }

        $iban = $this->input('iban');
        if (is_string($iban)) {
            $iban = strtoupper(preg_replace('/[\s-]+/u', '', $this->normalizeDigits($iban)));
            $this->merge(['iban' => $iban !== '' && !str_starts_with($iban, 'IR') ? 'IR' . $iban : $iban]);
        }
        $cardNumber = $this->input('card_number');
        if (is_string($cardNumber)) {
            $this->merge(['card_number' => preg_replace('/[\s-]+/u', '', $this->normalizeDigits($cardNumber))]);
        }
        foreach (['account_holder', 'user_note'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return route('account.wallet') . '#wallet-withdrawal';
    }
}
