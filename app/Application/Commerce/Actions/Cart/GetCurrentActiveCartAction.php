<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;

class GetCurrentActiveCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction $resolveCurrentCartOwnerAction,
    )
    {
    }

    public function execute(?CartOwnerDTO $owner = null): ?Cart
    {
        $owner ??= $this->resolveCurrentCartOwnerAction->execute();

        if ($owner->isAuthenticated()) {
            return Cart::query()
                ->with(['items.purchasable'])
                ->where('user_id', $owner->userId)
                ->where('status', CartStatusEnum::ACTIVE)
                ->latest('id')
                ->first();
        }

        return Cart::query()
            ->with(['items.purchasable'])
            ->whereNull('user_id')
            ->where('session_id', $owner->sessionId)
            ->where('status', CartStatusEnum::ACTIVE)
            ->latest('id')
            ->first();
    }
}
