<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Actions\Payment\CreatePaymentForPayableAction;
use App\Application\Financial\Actions\Payment\StartCreatedPaymentAction;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartWalletTopUpPaymentAction
{
    public function __construct(
        private readonly CreatePaymentForPayableAction $createPaymentForPayableAction,
        private readonly StartCreatedPaymentAction     $startCreatedPaymentAction,
    )
    {
    }

    /**
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        WalletTopUp        $topUp,
        PaymentGatewayEnum $gateway,
        string             $callbackUrl,
        array              $metadata = [],
    ): Payment
    {
        if (!$gateway->isExternal()) {
            throw new DomainException(
                'Wallet top-up requires an external payment gateway.'
            );
        }

        $payment = DB::transaction(function () use (
            $topUp,
            $gateway,
            $callbackUrl,
            $metadata,
        ): Payment {
            /** @var WalletTopUp $topUp */
            $topUp = WalletTopUp::query()
                ->whereKey($topUp->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($topUp->payment_id !== null) {
                return Payment::query()
                    ->findOrFail($topUp->payment_id);
            }

            if (!$topUp->canStartPayment()) {
                throw new DomainException(
                    'Wallet top-up cannot start payment.'
                );
            }

            $payment = $this->createPaymentForPayableAction->execute(
                payable: $topUp,
                gateway: $gateway,
                requestPayload: [
                    'callback_url' => $callbackUrl,
                    'metadata' => $metadata,
                ],
            );

            $topUp->forceFill([
                'payment_id' => $payment->id,
            ])->save();

            return $payment;
        }, 3);

        return $this->startCreatedPaymentAction->execute(
            payment: $payment,
            callbackUrl: $callbackUrl,
            metadata: $metadata,
        );
    }
}
