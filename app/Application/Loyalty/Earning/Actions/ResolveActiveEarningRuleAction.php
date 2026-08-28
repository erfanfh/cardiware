<?php

namespace App\Application\Loyalty\Earning\Actions;

use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use Carbon\CarbonInterface;

class ResolveActiveEarningRuleAction
{
    public function execute(
        EarningRuleTypeEnum $type,
        CarbonInterface $occurredAt,
    ): ?EarningRule {
        return EarningRule::query()
            ->where('type', $type->value)
            ->activeAt($occurredAt)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->first();
    }
}
