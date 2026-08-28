<?php

namespace App\Application\Loyalty\Earning\Exceptions;

use App\Domain\Loyalty\Models\EarningRule;
use DomainException;

class InvalidEarningRuleConfigurationException extends DomainException
{
    public static function forKey(EarningRule $rule, string $key, string $message): self
    {
        return new self(
            "Invalid config for earning rule [{$rule->code}] at [{$key}]: {$message}"
        );
    }
}
