<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranianIban implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^IR[0-9]{24}$/', $value)) {
            $fail('شماره شبا باید شامل ۲۴ رقم معتبر باشد.');
        }
    }
}
