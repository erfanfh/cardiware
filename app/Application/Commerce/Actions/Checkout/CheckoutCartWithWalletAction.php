<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Financial\Models\Payment;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutCartWithWalletAction
{
    public function __construct(
        private readonly CreateOrderFromCartAction $createOrderFromCartAction,
        private readonly PayOrderWithWalletAction  $payOrderWithWalletAction,
    )
    {
    }

    /**
     * Create the order and pay it completely with wallet balance.
     *
     * If any part fails, order creation, cart conversion, payment and
     * wallet debit are all rolled back.
     *
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        Cart  $cart,
        User  $user,
        array $metadata = [],
    ): Payment
    {
        return DB::transaction(function () use (
            $cart,
            $user,
            $metadata,
        ): Payment {
            /*
             * Locking the cart prevents two simultaneous checkout
             * requests from converting the same cart twice.
             */
            $cart = Cart::query()
                ->whereKey($cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int)$cart->user_id !== (int)$user->id) {
                throw new DomainException(
                    'The cart does not belong to this user.'
                );
            }

            $order = $this->createOrderFromCartAction->execute(
                cart: $cart,
                metadata: $metadata,
            );

            return $this->payOrderWithWalletAction->execute(
                order: $order,
                user: $user,
            );
        }, 3);
    }
}
