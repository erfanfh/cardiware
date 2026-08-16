<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\ReviewEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Application\Loyalty\Earning\Support\EarningRuleConfigReader;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Application\Loyalty\Earning\Support\EarningRuleUsageCounter;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;

class ReviewEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
        private readonly EarningRuleUsageCounter $usageCounter,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::REVIEW;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof ReviewEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof ReviewEarningContext) {
            throw UnexpectedEarningContextException::forType($this->type(), $context);
        }

        $comment = $context->comment;
        $user = $context->user;

        if (
            !$comment->exists
            || $comment->trashed()
            || $comment->user_id === null
            || (int)$comment->user_id !== (int)$user->getKey()
        ) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::REFERENCE_INVALID,
            );
        }

        if ($comment->isReply() || $comment->rating_percent === null) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::NOT_APPLICABLE,
            );
        }

        $requireApproval = $this->config->bool($rule, 'require_approval', true);

        if ($requireApproval && !$comment->isApproved()) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::NOT_APPROVED,
            );
        }

        $points = $this->config->positiveInt($rule, 'points');
        $dailyLimit = $this->config->positiveInt($rule, 'daily_limit');
        $usedToday = $this->usageCounter->countAwardsOnDate(
            $user,
            $rule,
            $context->occurredAt,
        );

        if ($usedToday >= $dailyLimit) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::DAILY_LIMIT_REACHED,
                [
                    'daily_limit' => $dailyLimit,
                    'used_today' => $usedToday,
                ],
            );
        }

        return EarningRuleEvaluation::award(
            rule: $rule,
            points: $points,
            reason: EarningRuleTypeEnum::REVIEW->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $comment,
            description: 'امتیاز ثبت نقد و بررسی',
            metadata: [
                'event_type' => EarningRuleTypeEnum::REVIEW->value,
                'commentable_type' => $comment->commentable_type,
                'commentable_id' => $comment->commentable_id,
                'rating_percent' => $comment->rating_percent,
                'daily_limit' => $dailyLimit,
            ],
        );
    }
}
