<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;

final class RemoveProductFavoriteAction
{
    public function __construct(
        private readonly UserCacheInvalidator $userCacheInvalidator,
    )
    {
    }

    public function execute(User $user, Product|int $product): bool
    {
        $productId = $product instanceof Product
            ? $product->getKey()
            : $product;

        $removed = $user->favoriteProducts()
                ->detach($productId) > 0;

        if ($removed) {
            $this->userCacheInvalidator->flushAccountForUser($user);
        }

        return $removed;
    }
}
