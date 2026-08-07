<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetUserFavoriteProductIdsAction
{
    /**
     * Get the user's favorite product IDs ordered from newest to oldest.
     *
     * @return Collection<int, int>
     */
    public function execute(User $user): Collection
    {
        return Cache::tags([
            CacheTag::user($user->id),
        ])->remember(
            CacheKey::userFavoriteProductIds($user->id),
            CacheTtl::long(),
            fn() => $user->favoriteProducts()
                ->orderByPivot('created_at', 'desc')
                ->pluck('products.id')
                ->map(static fn($id): int => (int)$id)
                ->values()
        );
    }
}
