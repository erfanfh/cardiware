<?php

namespace App\Application\Loyalty\Actions\RewardOffer;

use App\Domain\Loyalty\Models\RewardOffer;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteRewardOfferAction
{
    /** @throws Throwable */
    public function execute(RewardOffer $rewardOffer): void
    {
        DB::transaction(function () use ($rewardOffer): void {
            RewardOffer::query()
                ->whereKey($rewardOffer->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });
    }
}
