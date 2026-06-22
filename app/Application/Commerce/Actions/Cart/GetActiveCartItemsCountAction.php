<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\CartItem;

class GetActiveCartItemsCountAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction $resolveCurrentCartOwnerAction,
    )
    {
    }

    public function execute(?CartOwnerDTO $owner = null): int
    {
        $owner ??= $this->resolveCurrentCartOwnerAction->execute();

        return (int)CartItem::query()
            ->whereNull('parent_cart_item_id')
            ->where('cart_id', function ($sub) use ($owner) {
                $sub->select('id')
                    ->from('carts')
                    ->where('status', CartStatusEnum::ACTIVE)
                    ->when(
                        $owner->isAuthenticated(),
                        fn($q) => $q->where('user_id', $owner->userId),
                        fn($q) => $q->whereNull('user_id')
                            ->where('session_id', $owner->sessionId),
                    )
                    ->latest('id')
                    ->limit(1);
            })
            ->sum('quantity');
    }
}
