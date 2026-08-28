<?php

namespace App\Application\Loyalty\Earning\Exceptions;

use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use LogicException;

class UnexpectedEarningContextException extends LogicException
{
    public static function forType(EarningRuleTypeEnum $type, object $context): self
    {
        return new self(sprintf(
            'Earning rule type [%s] does not support context [%s].',
            $type->value,
            $context::class,
        ));
    }
}
