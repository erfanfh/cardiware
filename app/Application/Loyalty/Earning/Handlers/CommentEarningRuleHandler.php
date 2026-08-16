<?php

namespace App\Application\Loyalty\Earning\Handlers;

use App\Application\Loyalty\Earning\Contexts\CommentEarningContext;
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

class CommentEarningRuleHandler implements EarningRuleHandler
{
    public function __construct(
        private readonly EarningRuleConfigReader $config,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeys,
        private readonly EarningRuleUsageCounter $usageCounter,
    ) {
    }

    public function type(): EarningRuleTypeEnum
    {
        return EarningRuleTypeEnum::COMMENT;
    }

    public function supports(EarningContext $context): bool
    {
        return $context instanceof CommentEarningContext;
    }

    public function evaluate(EarningRule $rule, EarningContext $context): EarningRuleEvaluation
    {
        if (!$context instanceof CommentEarningContext) {
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

        $includeReplies = $this->config->bool($rule, 'include_replies', false);

        if (!$includeReplies && $comment->isReply()) {
            return EarningRuleEvaluation::skip(
                $rule,
                EarningRuleSkipReasonEnum::NOT_APPLICABLE,
            );
        }

        if (!$comment->isApproved()) {
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
            reason: EarningRuleTypeEnum::COMMENT->value,
            idempotencyKey: $this->idempotencyKeys->make($this->type(), $context),
            reference: $comment,
            description: 'امتیاز ثبت دیدگاه',
            metadata: [
                'event_type' => EarningRuleTypeEnum::COMMENT->value,
                'commentable_type' => $comment->commentable_type,
                'commentable_id' => $comment->commentable_id,
                'is_reply' => $comment->isReply(),
                'daily_limit' => $dailyLimit,
            ],
        );
    }
}
