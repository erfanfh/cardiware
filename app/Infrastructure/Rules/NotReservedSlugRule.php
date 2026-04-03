<?php

namespace App\Infrastructure\Rules;

use App\Application\Content\Services\ReservedSlugService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class NotReservedSlugRule implements ValidationRule
{
    public function __construct(
        private readonly ReservedSlugService $reservedSlugService,
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = trim((string)$value);

        if ($slug === '') {
            return;
        }

        if ($this->reservedSlugService->isReserved($slug)) {
            $fail('The selected slug is reserved and cannot be used.');
        }
    }
}
