<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\MembershipAnniversaryEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class MembershipAnniversaryEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof MembershipAnniversaryEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof MembershipAnniversaryEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $minimumMembershipYears = $this->config->positiveInt(
            $rule,
            'minimum_membership_years',
        );

        if ($context->membershipYears < $minimumMembershipYears) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::BELOW_MINIMUM,
                [
                    'membership_years' => $context->membershipYears,
                    'minimum_membership_years' => $minimumMembershipYears,
                ],
            );
        }

        $points = $this->config->positiveInt($rule, 'points');
        $anniversaryYear = $context->occurredAt
            ->setTimezone(config('app.timezone'))
            ->format('Y');

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $context->user,
            description: "امتیاز سالگرد {$context->membershipYears} سالگی عضویت",
            metadata: [
                'event_type' => EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY->value,
                'membership_years' => $context->membershipYears,
                'anniversary_year' => (int)$anniversaryYear,
            ],
        );
    }
}
