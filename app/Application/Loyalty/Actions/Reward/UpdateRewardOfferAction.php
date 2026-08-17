<?php

namespace App\Application\Loyalty\Actions\Reward;

use App\Domain\Loyalty\Models\RewardOffer;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateRewardOfferAction
{
    /** @throws Throwable */
    public function execute(RewardOffer $rewardOffer, array $data): RewardOffer
    {
        return DB::transaction(function () use ($rewardOffer, $data): RewardOffer {
            $rewardOffer->update([
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'points_cost' => (int)$data['points_cost'],
                'is_active' => (bool)$data['is_active'],
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'redemption_limit' => $data['redemption_limit'] ?? null,
                'redemption_limit_per_user' => $data['redemption_limit_per_user'] ?? null,
                'config' => $data['config'],
            ]);

            return $rewardOffer->refresh();
        });
    }
}
