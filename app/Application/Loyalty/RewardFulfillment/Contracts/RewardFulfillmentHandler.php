<?php

namespace App\Application\Loyalty\RewardFulfillment\Contracts;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use Illuminate\Database\Eloquent\Model;

interface RewardFulfillmentHandler
{
    public function type(): RewardTypeEnum;

    public function fulfill(
        RewardOffer $offer,
        RewardRedemption $redemption,
        User $user,
    ): Model;
}
