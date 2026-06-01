<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use DomainException;

class MarkCartAsCheckoutStartedAction
{
    /**
     * Mark the cart as checkout started.
     */
    public function execute(Cart $cart): Cart
    {
        if (!$cart->isEditable()) {
            throw new DomainException('Only active carts can start checkout.');
        }

        if ($cart->items()->doesntExist()) {
            throw new DomainException('Empty carts cannot start checkout.');
        }

        $cart->forceFill([
            'status' => CartStatusEnum::CHECKOUT_STARTED,
        ])->save();

        return $cart->refresh();
    }
}
