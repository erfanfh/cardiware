<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutCartWithWalletShortfallPaymentAction
{
    public function __construct(
        private readonly CreateOrderFromCartAction              $createOrderFromCartAction,
        private readonly StartOrderWalletShortfallPaymentAction $startShortfallPaymentAction,
    )
    {
    }

    /**
     * Create the order, reserve available wallet balance and prepare
     * an external payment only for the shortage.
     *
     * Gateway HTTP communication happens after the transaction commits.
     *
     * @param array<string, mixed> $metadata
     *
     * @throws Throwable
     */
    public function execute(
        Cart               $cart,
        User               $user,
        PaymentGatewayEnum $gateway,
        string             $requestKey,
        string             $callbackUrl,
        array              $metadata = [],
    ): Payment
    {
        $payment = DB::transaction(function () use (
            $cart,
            $user,
            $gateway,
            $requestKey,
            $callbackUrl,
            $metadata,
        ): Payment {
            /** @var Cart $cart */
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

            /*
             * prepare() only performs database operations.
             * Because we are already inside a transaction, order creation,
             * hold creation and payment preparation are atomic.
             */
            return $this->startShortfallPaymentAction->prepare(
                order: $order,
                user: $user,
                gateway: $gateway,
                requestKey: $requestKey,
                callbackUrl: $callbackUrl,
                metadata: $metadata,
            );
        }, 3);

        /*
         * HTTP request to the gateway must happen after the outer
         * transaction has committed.
         */
        return $this->startShortfallPaymentAction
            ->startPreparedPayment(
                payment: $payment,
                callbackUrl: $callbackUrl,
                metadata: $metadata,
            );
    }
}
