<?php

namespace App\Application\Loyalty\RewardFulfillment\Support;

use App\Application\Loyalty\RewardFulfillment\Contracts\RewardFulfillmentHandler;
use App\Application\Loyalty\RewardFulfillment\Handlers\CouponRewardFulfillmentHandler;
use App\Application\Loyalty\RewardFulfillment\Handlers\RaffleTicketRewardFulfillmentHandler;
use App\Domain\Loyalty\Enums\RewardTypeEnum;

class RewardFulfillmentHandlerResolver
{
    public function __construct(
        private readonly CouponRewardFulfillmentHandler $couponHandler,
        private readonly RaffleTicketRewardFulfillmentHandler $raffleTicketHandler,
    ) {
    }

    public function resolve(RewardTypeEnum $type): RewardFulfillmentHandler
    {
        return match ($type) {
            RewardTypeEnum::COUPON => $this->couponHandler,
            RewardTypeEnum::RAFFLE_TICKET => $this->raffleTicketHandler,
        };
    }
}
