<?php

namespace App\Application\Loyalty\Earning\Support;

use App\Application\Loyalty\Earning\Contexts\CommentEarningContext;
use App\Application\Loyalty\Earning\Contexts\DailyVisitEarningContext;
use App\Application\Loyalty\Earning\Contexts\MembershipAnniversaryEarningContext;
use App\Application\Loyalty\Earning\Contexts\PurchaseEarningContext;
use App\Application\Loyalty\Earning\Contexts\RegistrationEarningContext;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\Exceptions\UnexpectedEarningContextException;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;

class EarningIdempotencyKeyFactory
{
    public function make(
        EarningRuleTypeEnum $type,
        EarningContext $context,
    ): string {
        return match ($type) {
            EarningRuleTypeEnum::REGISTRATION => $this->registration($type, $context),
            EarningRuleTypeEnum::DAILY_VISIT => $this->dailyVisit($type, $context),
            EarningRuleTypeEnum::PURCHASE => $this->purchase($type, $context),
            EarningRuleTypeEnum::COMMENT => $this->comment($type, $context),
            EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY => $this->membershipAnniversary($type, $context),
        };
    }

    private function registration(EarningRuleTypeEnum $type, EarningContext $context): string
    {
        if (!$context instanceof RegistrationEarningContext) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        return "loyalty:earn:registration:user:{$context->user->getKey()}";
    }

    private function dailyVisit(EarningRuleTypeEnum $type, EarningContext $context): string
    {
        if (!$context instanceof DailyVisitEarningContext) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        $date = $context->occurredAt
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d');

        return "loyalty:earn:daily_visit:user:{$context->user->getKey()}:{$date}";
    }

    private function purchase(EarningRuleTypeEnum $type, EarningContext $context): string
    {
        if (!$context instanceof PurchaseEarningContext) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        $occurrence = $this->occurrenceToken($context);

        return "loyalty:earn:purchase:order:{$context->order->getKey()}:{$occurrence}";
    }

    private function comment(EarningRuleTypeEnum $type, EarningContext $context): string
    {
        if (!$context instanceof CommentEarningContext) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        $occurrence = $this->occurrenceToken($context);

        return "loyalty:earn:comment:comment:{$context->comment->getKey()}:{$occurrence}";
    }

    private function membershipAnniversary(
        EarningRuleTypeEnum $type,
        EarningContext $context,
    ): string {
        if (!$context instanceof MembershipAnniversaryEarningContext) {
            throw UnexpectedEarningContextException::forType($type, $context);
        }

        $year = $context->occurredAt
            ->setTimezone(config('app.timezone'))
            ->format('Y');

        return "loyalty:earn:membership_anniversary:user:{$context->user->getKey()}:{$year}";
    }

    private function occurrenceToken(EarningContext $context): string
    {
        return $context->occurredAt()
            ->utc()
            ->format('YmdHis.u');
    }
}
