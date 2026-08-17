<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Domain\Loyalty\Models\RewardOffer;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteRewardOfferAction
{
    /** @throws Throwable */
    public function execute(RewardOffer $rewardOffer): void
    {
        DB::transaction(function () use ($rewardOffer): void {
            $rewardOffer->delete();
        });
    }
}
