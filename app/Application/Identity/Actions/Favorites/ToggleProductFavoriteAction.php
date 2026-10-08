<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

final class ToggleProductFavoriteAction
{
    public function __construct(
        private readonly IsProductFavoriteAction $isProductFavoriteAction,
        private readonly UserCacheInvalidator $userCacheInvalidator,
    )
    {
    }

    /**
     * Toggle favorite state and return the new state.
     */
    public function execute(User $user, Product $product): bool
    {
        $isFavorite = $this->isProductFavoriteAction->execute($user, $product);

        $newState = DB::transaction(function () use ($user, $product, $isFavorite): bool {
            if ($isFavorite) {
                app(AuditService::class)->detach($user, 'favoriteProducts', [$product->getKey()]);

                return false;
            }

            app(AuditService::class)->sync($user, 'favoriteProducts', [$product->getKey()], false);

            return true;
        });

        $this->userCacheInvalidator->flushAccountForUser($user);

        return $newState;
    }
}
