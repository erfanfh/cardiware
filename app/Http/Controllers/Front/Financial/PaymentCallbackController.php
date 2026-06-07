<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Commerce\Actions\Checkout\CompletePaidOrderPaymentAction;
use App\Application\Financial\Actions\Payment\VerifyPaymentAction;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentCallbackController extends Controller
{
    public function __invoke(
        string                         $gateway,
        Request                        $request,
        VerifyPaymentAction            $verifyPaymentAction,
        CompletePaidOrderPaymentAction $completePaidOrderPaymentAction,
    ): RedirectResponse
    {
        $gatewayReference = $request->input('trackId')
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

        if (!$gatewayReference) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'شناسه پرداخت از درگاه دریافت نشد.');
        }

        $payment = Payment::query()
            ->where('gateway_reference', (string)$gatewayReference)
            ->first();

        if (!$payment) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'پرداخت مورد نظر یافت نشد.');
        }

        try {
            $payment = $verifyPaymentAction->execute(
                payment: $payment,
                callbackPayload: $request->all(),
            );

            if ($payment->isPaid()) {
                $completePaidOrderPaymentAction->execute($payment);

                return redirect()->route('payment.success', [
                    'payment' => $payment->tracking_code,
                ]);
            }

            return redirect()->route('payment.failed', [
                'payment' => $payment->tracking_code,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('payment.failed', [
                'payment' => $payment->tracking_code,
            ]);
        }
    }
}
