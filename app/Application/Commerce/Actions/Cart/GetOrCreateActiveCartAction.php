<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;

class GetOrCreateActiveCartAction
{
    public function __construct(
        private readonly ResolveCurrentCartOwnerAction $resolveCurrentCartOwnerAction,
    )
    {
    }

    /**
     * Get active cart for current owner or create a new one.
     */
    public function execute(?CartOwnerDTO $owner = null): Cart
    {
        $owner ??= $this->resolveCurrentCartOwnerAction->execute();

        if ($owner->isAuthenticated()) {
            return $this->getOrCreateUserCart($owner);
        }

        return $this->getOrCreateGuestCart($owner);
    }

    /**
     * Get or create authenticated user's active cart.
     */
    private function getOrCreateUserCart(CartOwnerDTO $owner): Cart
    {
        $cart = Cart::query()
            ->where('user_id', $owner->userId)
            ->where('status', CartStatusEnum::ACTIVE)
            ->latest('id')
            ->first();

        if ($cart instanceof Cart) {
            return $cart;
        }

        return Cart::query()->create([
            'user_id' => $owner->userId,
            'session_id' => $owner->sessionId,
            'status' => CartStatusEnum::ACTIVE,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);
    }

    /**
     * Get or create guest user's active cart.
     */
    private function getOrCreateGuestCart(CartOwnerDTO $owner): Cart
    {
        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $owner->sessionId)
            ->where('status', CartStatusEnum::ACTIVE)
            ->latest('id')
            ->first();

        if ($cart instanceof Cart) {
            return $cart;
        }

        return Cart::query()->create([
            'user_id' => null,
            'session_id' => $owner->sessionId,
            'status' => CartStatusEnum::ACTIVE,
            'expires_at' => now()->addDays(7),
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);
    }
}
