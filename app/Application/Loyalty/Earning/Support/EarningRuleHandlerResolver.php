<?php

namespace App\Application\Loyalty\Earning\Support;

use App\Application\Loyalty\Earning\Contracts\EarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\CommentEarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\DailyVisitEarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\MembershipAnniversaryEarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\PurchaseEarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\RegistrationEarningRuleHandler;
use App\Application\Loyalty\Earning\Handlers\TimeSpentEarningRuleHandler;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;

class EarningRuleHandlerResolver
{
    public function __construct(
        private readonly RegistrationEarningRuleHandler $registrationHandler,
        private readonly DailyVisitEarningRuleHandler $dailyVisitHandler,
        private readonly PurchaseEarningRuleHandler $purchaseHandler,
        private readonly CommentEarningRuleHandler $commentHandler,
        private readonly MembershipAnniversaryEarningRuleHandler $membershipAnniversaryHandler,
        private readonly TimeSpentEarningRuleHandler $timeSpentHandler,
    ) {
    }

    public function resolve(EarningRuleTypeEnum $type): EarningRuleHandler
    {
        return match ($type) {
            EarningRuleTypeEnum::REGISTRATION => $this->registrationHandler,
            EarningRuleTypeEnum::DAILY_VISIT => $this->dailyVisitHandler,
            EarningRuleTypeEnum::PURCHASE => $this->purchaseHandler,
            EarningRuleTypeEnum::COMMENT => $this->commentHandler,
            EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY => $this->membershipAnniversaryHandler,
            EarningRuleTypeEnum::TIME_SPENT => $this->timeSpentHandler,
        };
    }
}
