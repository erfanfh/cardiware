<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Application\Commerce\Support\TaxCalculator;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;

class GetCurrentActiveCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction $resolveCurrentCartOwnerAction,
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
        private readonly TaxCalculator $taxCalculator,
    )
    {
    }

    public function execute(?CartOwnerDTO $owner = null): ?Cart
    {
        $owner ??= $this->resolveCurrentCartOwnerAction->execute();

        if ($owner->isAuthenticated()) {
            $cart = Cart::query()
                ->with(['items.purchasable'])
                ->where('user_id', $owner->userId)
                ->where('status', CartStatusEnum::ACTIVE)
                ->latest('id')
                ->first();
        } else {
            $cart = Cart::query()
                ->with(['items.purchasable'])
                ->whereNull('user_id')
                ->where('session_id', $owner->sessionId)
                ->where('status', CartStatusEnum::ACTIVE)
                ->latest('id')
                ->first();
        }

        if ($cart && (int)$cart->tax_rate_basis_points !== $this->taxCalculator->currentRateBasisPoints()) {
            $cart = $this->recalculateCartTotalsAction->execute($cart);
        }

        return $cart;
    }
}
