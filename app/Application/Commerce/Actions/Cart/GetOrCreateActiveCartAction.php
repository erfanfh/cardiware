<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;

class GetOrCreateActiveCartAction
{
    /**
     * Get the active cart for a user or create a new one.
     */
    public function execute(int $userId): Cart
    {
        $cart = Cart::query()
            ->where('user_id', $userId)
            ->where('status', CartStatusEnum::ACTIVE)
            ->latest('id')
            ->first();

        if ($cart instanceof Cart) {
            return $cart;
        }

        return Cart::query()->create([
            'user_id' => $userId,
            'status' => CartStatusEnum::ACTIVE,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);
    }
}
