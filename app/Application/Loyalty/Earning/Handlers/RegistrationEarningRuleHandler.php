<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\RegistrationEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class RegistrationEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::REGISTRATION;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof RegistrationEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof RegistrationEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $points = $this->config->positiveInt($rule, 'points');
        $user = $context->user;

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::REGISTRATION->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $user,
            description: 'امتیاز ثبت نام در سایت',
            metadata: [
                'event_type' => EarningRuleTypeEnum::REGISTRATION->value,
                'registered_at' => $user->created_at?->toISOString(),
            ],
        );
    }
}
