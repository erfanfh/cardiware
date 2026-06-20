<?php

namespace App\Application\Identity\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;

final class GetAccountUserStatsAction
{
    /**
     * Get cached account stats for the given user.
     *
     * @return array{
     *     paid_orders_count: int,
     *     purchase_accesses_count: int
     * }
     */
    public function execute(User $user): array
    {
        return Cache::tags([
            CacheTag::ACCOUNT,
            CacheTag::user($user->id),
        ])->remember(
            CacheKey::accountUserStats($user->id),
            CacheTtl::short(),
            fn() => $this->query($user)
        );
    }

    /**
     * Query account stats for the given user.
     *
     * @return array{
     *     paid_orders_count: int,
     *     purchase_accesses_count: int
     * }
     */
    private function query(User $user): array
    {
        return [
            'paid_orders_count' => $user->orders()
                ->where('status', OrderStatusEnum::PAID)
                ->count(),

            'purchase_accesses_count' => $user->purchaseAccesses()
                ->count(),
        ];
    }
}
