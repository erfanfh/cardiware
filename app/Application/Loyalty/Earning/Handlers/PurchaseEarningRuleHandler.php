<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Actions\ResolveUserLevelAction;
use App\Application\Loyalty\Earning\Contexts\PurchaseEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class PurchaseEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
        private readonly ResolveUserLevelAction $resolveUserLevel,
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

        // total_amount in the current commerce model is the final product amount after discount.
        $eligibleAmount = max(0, (int)$order->total_amount);
        [$basePoints, $amountStep, $pointsPerStep, $minimumOrderAmount, $maximumPointsPerOrder] =
            $this->calculateBasePoints($rule, $eligibleAmount);

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

        if ($basePoints <= 0) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::ZERO_POINTS,
                [
                    'eligible_amount' => $eligibleAmount,
                    'amount_step' => $amountStep,
                ],
            );
        }

        // ضریب سطح کاربر روی امتیاز خرید؛ سطح از lifetime_xp تعیین می‌شود.
        $level = $this->resolveUserLevel->execute($user->pointAccount()->first());
        $multiplier = (float) $level->purchase_multiplier;
        $points = $this->applyMultiplier($basePoints, $multiplier);

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
                'steps' => intdiv($eligibleAmount, $amountStep),
                'points_per_step' => $pointsPerStep,
                'max_points_per_order' => $maximumPointsPerOrder,
                'base_points' => $basePoints,
                'level_id' => $level->getKey(),
                'level_title' => $level->title,
                'level_multiplier' => $multiplier,
            ],
        );
    }

    public function estimate(EarningRule $rule, int $amount, ?User $user): int
    {
        [$points] = $this->calculateBasePoints($rule, $amount);
        if ($user === null || $points === 0) {
            return $points;
        }

        $level = $this->resolveUserLevel->execute($user->pointAccount()->first());

        return $this->applyMultiplier($points, (float) $level->purchase_multiplier);
    }

    /** @return array{int, int, int, int, int|null} */
    private function calculateBasePoints(EarningRule $rule, int $amount): array
    {
        $amountStep = $this->config->positiveInt($rule, 'amount_step');
        $pointsPerStep = $this->config->positiveInt($rule, 'points');
        $minimumAmount = $this->config->nonNegativeInt($rule, 'minimum_order_amount');
        $maxPoints = $this->config->nullablePositiveInt($rule, 'max_points_per_order');
        $points = $amount < $minimumAmount ? 0 : intdiv($amount, $amountStep) * $pointsPerStep;

        return [$maxPoints === null ? $points : min($points, $maxPoints), $amountStep, $pointsPerStep, $minimumAmount, $maxPoints];
    }

    private function applyMultiplier(int $points, float $multiplier): int
    {
        return $multiplier > 0 ? (int) round($points * $multiplier) : $points;
    }
}
