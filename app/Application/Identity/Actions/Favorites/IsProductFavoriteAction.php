<?php

namespace App\Application\Identity\Actions\Favorites;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;

final class IsProductFavoriteAction
{
    public function __construct(
        private readonly GetUserFavoriteProductIdsAction $getUserFavoriteProductIdsAction,
    )
    {
    }

    public function execute(User $user, Product $product): bool
    {
        return $this->getUserFavoriteProductIdsAction
            ->execute($user)
            ->contains((int)$product->getKey());
    }
}
