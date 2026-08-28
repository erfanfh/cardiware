<?php

namespace App\Application\Loyalty\Earning\Actions;

use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleHandlerResolver;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;

class EvaluateEarningRuleAction
{
    public function __construct(
        private readonly ResolveActiveEarningRuleAction $resolveActiveRuleAction,
        private readonly EarningRuleHandlerResolver $handlerResolver,
    ) {
    }

    public function execute(
        EarningRuleTypeEnum $type,
        EarningContext $context,
    ): EarningRuleEvaluation {
        $rule = $this->resolveActiveRuleAction->execute(
            $type,
            $context->occurredAt(),
        );

        if (!$rule) {
            return EarningRuleEvaluation::skip(
                rule: null,
                skipReason: EarningRuleSkipReasonEnum::NO_ACTIVE_RULE,
                metadata: ['rule_type' => $type->value],
            );
        }

        $handler = $this->handlerResolver->resolve($type);

        if (!$handler->supports($context)) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        return $handler->evaluate($rule, $context);
    }
}
