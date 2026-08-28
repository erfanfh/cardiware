<?php

namespace App\Application\Loyalty\Earning\Contracts;

use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

interface EarningRuleHandler
{
    public function type(): EarningRuleTypeEnum;

    public function supports(EarningContext $context): bool;

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation;
}
