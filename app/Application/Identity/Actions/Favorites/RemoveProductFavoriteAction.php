<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Audit\AuditService;

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

        $removed = app(AuditService::class)->detach($user, 'favoriteProducts', [$productId]) > 0;

        if ($removed) {
            $this->userCacheInvalidator->flushAccountForUser($user);
        }

        return $removed;
    }
}
