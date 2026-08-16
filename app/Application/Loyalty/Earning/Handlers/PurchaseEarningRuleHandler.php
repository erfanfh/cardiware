<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\PurchaseEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class PurchaseEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::PURCHASE;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof PurchaseEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof PurchaseEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $order = $context->order;
        $user = $context->user;

        if (!$order->exists || (int)$order->user_id !== (int)$user->getKey()) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::REFERENCE_INVALID,
            );
        }

        if (!$order->isPaid()) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::NOT_APPLICABLE,
                ['order_status' => $order->status->value],
            );
        }

        $amountStep = $this->config->positiveInt($rule, 'amount_step');
        $pointsPerStep = $this->config->positiveInt($rule, 'points');
        $minimumOrderAmount = $this->config->nonNegativeInt($rule, 'minimum_order_amount');
        $maximumPointsPerOrder = $this->config->nullablePositiveInt($rule, 'max_points_per_order');

        // total_amount in the current commerce model is the final product amount after discount.
        $eligibleAmount = max(0, (int)$order->total_amount);

        if ($eligibleAmount < $minimumOrderAmount) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::BELOW_MINIMUM,
                [
                    'eligible_amount' => $eligibleAmount,
                    'minimum_order_amount' => $minimumOrderAmount,
                ],
            );
        }

        $steps = intdiv($eligibleAmount, $amountStep);
        $points = $steps * $pointsPerStep;

        if ($maximumPointsPerOrder !== null) {
            $points = min($points, $maximumPointsPerOrder);
        }

        if ($points <= 0) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::ZERO_POINTS,
                [
                    'eligible_amount' => $eligibleAmount,
                    'amount_step' => $amountStep,
                ],
            );
        }

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::PURCHASE->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $order,
            description: "امتیاز خرید سفارش {$order->order_number}",
            metadata: [
                'event_type' => EarningRuleTypeEnum::PURCHASE->value,
                'order_number' => $order->order_number,
                'eligible_amount' => $eligibleAmount,
                'amount_step' => $amountStep,
                'steps' => $steps,
                'points_per_step' => $pointsPerStep,
                'max_points_per_order' => $maximumPointsPerOrder,
            ],
        );
    }
}
