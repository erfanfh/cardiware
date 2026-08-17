<?php

namespace App\Application\Loyalty\Actions\RewardOffer;

use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateRewardOfferAction
{
    /**
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function execute(array $data): RewardOffer
    {
        return DB::transaction(function () use ($data): RewardOffer {
            $type = $data['type'] instanceof RewardTypeEnum
                ? $data['type']
                : RewardTypeEnum::from((string) $data['type']);

            [$targetType, $targetId] = $this->resolveTarget($type, $data['target_id'] ?? null);

            return RewardOffer::query()->create([
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $type,
                'points_cost' => (int) $data['points_cost'],
                'is_active' => (bool) $data['is_active'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'redemption_limit' => $data['redemption_limit'] ?? null,
                'redemption_limit_per_user' => $data['redemption_limit_per_user'] ?? null,
                'config' => $this->normalizedConfig($type, $data['config'] ?? []),
                'target_type' => $targetType,
                'target_id' => $targetId,
            ])->refresh();
        });
    }

    /** @return array{0:?string,1:int|null} */
    private function resolveTarget(RewardTypeEnum $type, mixed $targetId): array
    {
        if ($type !== RewardTypeEnum::RAFFLE_TICKET) {
            return [null, null];
        }

        $campaign = DrawCampaign::query()->findOrFail((int) $targetId);

        return [$campaign->getMorphClass(), (int) $campaign->getKey()];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function normalizedConfig(RewardTypeEnum $type, array $config): array
    {
        $keys = match ($type) {
            RewardTypeEnum::COUPON => [
                'coupon_type',
                'coupon_value',
                'max_discount_amount',
                'minimum_order_amount',
                'expires_after_days',
                'applies_to_addons',
            ],
            RewardTypeEnum::RAFFLE_TICKET => [
                'ticket_quantity',
            ],
        };

        return array_intersect_key($config, array_flip($keys));
    }

}
