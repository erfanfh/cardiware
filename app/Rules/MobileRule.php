<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class MobileRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!preg_match('/^09[0-9]{9}$/', $value)) {
            $fail(':attribute باید به فرمت صحیح (09xxxxxxxxx) باشد.');
        }
    }
}
