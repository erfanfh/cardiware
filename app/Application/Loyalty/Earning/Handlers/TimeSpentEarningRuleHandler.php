<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\TimeSpentEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class TimeSpentEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::TIME_SPENT;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof TimeSpentEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof TimeSpentEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $points = $this->config->positiveInt($rule, 'points');

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::TIME_SPENT->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $context->reference(),
            description: 'امتیاز حضور در صفحه محتوا',
            metadata: [
                'event_type' => EarningRuleTypeEnum::TIME_SPENT->value,
                'target_type' => $context->targetType,
                'target_id' => $context->targetId,
                'duration_seconds' => $context->durationSeconds,
            ],
        );
    }
}
