<?php

namespace App\Http\Requests\Front\Marketplace;

use App\Infrastructure\Services\Setting\Groups\FinancialSettings;
use App\Rules\IranianIban;
use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $settings = app(FinancialSettings::class);

        return [
            'amount' => [
                'required', 'integer',
                'min:'.$settings->get('store_withdrawal_minimum_amount', FinancialSettings::STORE_WITHDRAWAL_MINIMUM_AMOUNT),
                'max:'.$settings->get('store_withdrawal_maximum_amount', FinancialSettings::STORE_WITHDRAWAL_MAXIMUM_AMOUNT),
            ],
            'iban' => ['required', new IranianIban()],
            'card_number' => ['required', 'regex:/^[0-9]{16}$/'],
            'account_holder' => ['required', 'string', 'max:150'],
            'seller_note' => ['nullable', 'string', 'max:1000'],
            'request_key' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'مبلغ درخواست',
            'iban' => 'شماره شبا',
            'card_number' => 'شماره کارت',
            'account_holder' => 'نام صاحب حساب',
            'seller_note' => 'توضیحات',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizeDigits = static fn (string $value): string => strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        if (is_string($this->input('amount'))) {
            $this->merge(['amount' => preg_replace('/[,\x{066C}\x{060C}\s]+/u', '', $normalizeDigits($this->input('amount')))]);
        }
        if (is_string($this->input('iban'))) {
            $iban = strtoupper(preg_replace('/[\s-]+/u', '', $normalizeDigits($this->input('iban'))));
            $this->merge(['iban' => $iban !== '' && ! str_starts_with($iban, 'IR') ? 'IR'.$iban : $iban]);
        }
        if (is_string($this->input('card_number'))) {
            $this->merge(['card_number' => preg_replace('/[\s-]+/u', '', $normalizeDigits($this->input('card_number')))]);
        }
        foreach (['account_holder', 'seller_note'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }
}
