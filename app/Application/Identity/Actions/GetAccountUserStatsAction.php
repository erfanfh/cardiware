<?php

namespace App\Application\Identity\Actions;

use App\Application\Identity\Actions\Favorites\GetUserFavoriteProductIdsAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketStatusEnum;
use Illuminate\Support\Facades\Cache;

final class GetAccountUserStatsAction
{
    public function __construct(
        private readonly GetUserFavoriteProductIdsAction $getUserFavoriteProductIdsAction,
    )
    {
    }

    /**
     * Get cached account stats for the given user.
     *
     * @return array{
     *     paid_orders_count: int,
     *     purchase_accesses_count: int,
     *     favorite_products_count: int,
     *     tickets_count: int,
     *     open_tickets_count: int,
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
     *     purchase_accesses_count: int,
     *     favorite_products_count: int,
     *     tickets_count: int,
     *     open_tickets_count: int,
     * }
     */
    private function query(User $user): array
    {
        return [
            'paid_orders_count' => $user->orders()
                ->where('status', OrderStatusEnum::PAID)
                ->count(),

            'purchase_accesses_count' => $user->purchaseAccesses()
                ->owned()
                ->count(),

            'favorite_products_count' => $this->getUserFavoriteProductIdsAction
                ->execute($user)
                ->count(),

            'tickets_count' => $user->tickets()->count(),

            'open_tickets_count' => $user->tickets()->where('status', TicketStatusEnum::OPEN)->count(),
        ];
    }
}
