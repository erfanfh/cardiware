<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\DailyVisitEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class DailyVisitEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::DAILY_VISIT;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof DailyVisitEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof DailyVisitEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $points = $this->config->positiveInt($rule, 'points');
        $date = $context->occurredAt
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d');

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::DAILY_VISIT->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $context->user,
            description: 'امتیاز بازدید روزانه از سایت',
            metadata: [
                'event_type' => EarningRuleTypeEnum::DAILY_VISIT->value,
                'visit_date' => $date,
            ],
        );
    }
}
