<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\FinalizeVerifiedPaymentAction;
use App\Application\Financial\Actions\Payment\VerifyPaymentAction;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PaymentCallbackController extends Controller
{
    public function __invoke(
        string                        $gateway,
        Request                       $request,
        VerifyPaymentAction           $verifyPaymentAction,
        FinalizeVerifiedPaymentAction $finalizeVerifiedPaymentAction,
    ): Response
    {
        $gatewayEnum = PaymentGatewayEnum::tryFrom($gateway);

        if (!$gatewayEnum || !$gatewayEnum->isExternal()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'درگاه پرداخت نامعتبر است.');
        }

        $gatewayReference = $this->resolveGatewayReference($request);

        if (blank($gatewayReference)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'شناسه پرداخت از درگاه دریافت نشد.');
        }

        $payment = Payment::query()
            ->where('gateway', $gatewayEnum->value)
            ->where('gateway_reference', (string)$gatewayReference)
            ->first();

        if (!$payment) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'پرداخت موردنظر یافت نشد.');
        }

        /*
         * First, verify the external payment.
         *
         * VerifyPaymentAction is responsible for changing Payment to:
         *
         * - PAID
         * - FAILED
         * - CANCELLED
         * - EXPIRED
         * - or keeping it PENDING
         */
        try {
            if (!$payment->isPaid()) {
                $payment = $verifyPaymentAction->execute(
                    payment: $payment,
                    callbackPayload: $request->all(),
                );
            }
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Verification may have persisted part of its result before
             * another side effect failed, so reload the latest state.
             */
            $payment->refresh();
        }

        /*
         * Finalize both successful and unsuccessful terminal states.
         *
         * Successful:
         * - complete Order
         * - complete manual WalletTopUp
         * - complete order-shortfall WalletTopUp
         *
         * Failed:
         * - release coupon reservation
         * - fail manual WalletTopUp
         * - release WalletHold for shortfall payment
         */
        try {
            $finalizeVerifiedPaymentAction->execute($payment);
        } catch (Throwable $exception) {
            report($exception);

            /*
             * The result page will perform the same idempotent
             * finalization again as a self-healing mechanism.
             */
            $payment->refresh();
        }

        $resultUrl = route('payment.result', [
            'paymentToken' => $payment->public_token,
        ]);

        return response()->view(
            'front.financial.payments.callback',
            [
                'resultUrl' => $resultUrl,
            ],
            200,
        );
    }

    private function resolveGatewayReference(Request $request): ?string
    {
        $reference = $request->input('trackId')
            ?? $request->input('track_id')
            ?? $request->input('Authority')
            ?? $request->input('authority')
            ?? $request->input('refId')
            ?? $request->input('RefId')
            ?? $request->input('refid')
            ?? $request->input('referenceId')
            ?? $request->input('reference_id')
            ?? $request->input('code')
            ?? $request->input('Code');

        if ($reference === null) {
            return null;
        }

        $reference = trim((string)$reference);

        return $reference !== '' ? $reference : null;
    }
}
