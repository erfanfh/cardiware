<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class FailManualWalletTopUpPaymentAction
{
    /**
     * Mark a manual wallet top-up as failed after its external
     * payment reaches an unsuccessful terminal status.
     *
     * @throws Throwable
     */
    public function execute(Payment $payment, ?string $reason = null): WalletTopUp
    {
        $seedPayment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        $seedTopUp = $seedPayment->payable;

        if (!$seedTopUp instanceof WalletTopUp || !$seedTopUp->isManual()) {
            throw new DomainException(
                'Payment does not belong to a manual wallet top-up.'
            );
        }

        $paymentId = (int)$seedPayment->id;
        $topUpId = (int)$seedTopUp->id;

        return DB::transaction(function () use (
            $paymentId,
            $topUpId,
            $reason,
        ): WalletTopUp {
            /** @var Payment $payment */
            $payment = Payment::query()
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var WalletTopUp $topUp */
            $topUp = WalletTopUp::query()
                ->whereKey($topUpId)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * A successful payment may have won a race against
             * the failure handler. Never mark it as failed.
             */
            if ($payment->isPaid()) {
                return $topUp;
            }

            if (!$this->isUnsuccessfulTerminal($payment)) {
                throw new DomainException(
                    'Payment has not reached an unsuccessful terminal status.'
                );
            }

            if (!$topUp->isManual()) {
                throw new WalletIntegrityException(
                    'Wallet top-up is not a manual top-up.'
                );
            }

            if ((int)$topUp->payment_id !== (int)$payment->id) {
                throw new WalletIntegrityException(
                    'Wallet top-up payment does not match the failed payment.'
                );
            }

            if (
                $payment->payable_type !== $topUp->getMorphClass()
                || (int)$payment->payable_id !== (int)$topUp->id
            ) {
                throw new WalletIntegrityException(
                    'Failed payment payable does not match wallet top-up.'
                );
            }

            if ($topUp->isCompleted()) {
                throw new WalletIntegrityException(
                    'Completed wallet top-up cannot be marked as failed.'
                );
            }

            /*
             * Idempotent duplicate failure handling.
             */
            if ($topUp->status->isTerminal()) {
                return $topUp->loadMissing([
                    'wallet',
                    'payment',
                    'creditedTransaction',
                ]);
            }

            $topUp->forceFill([
                'status' => WalletTopUpStatusEnum::FAILED,
                'failed_at' => now(),
                'failure_reason' => $reason ?: $payment->gateway_message ?: 'External wallet top-up payment failed.',
            ])->save();

            return $topUp->refresh()->load([
                'wallet',
                'payment',
            ]);
        }, 3);
    }

    private function isUnsuccessfulTerminal(
        Payment $payment,
    ): bool
    {
        return in_array($payment->status, [
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED,
        ], true);
    }
}
