<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('رمز عبور معتبر نیست.');
            return;
        }

        if (mb_strlen($value) < 8) {
            $fail('رمز عبور باید حداقل ۸ کاراکتر باشد.');
        }
    }
}
