<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\CompletePaidPaymentAction;
use App\Application\Financial\Actions\Payment\VerifyPaymentAction;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentCallbackController extends Controller
{
    public function __invoke(
        string                    $gateway,
        Request                   $request,
        VerifyPaymentAction       $verifyPaymentAction,
        CompletePaidPaymentAction $completePaidPaymentAction,
    ): RedirectResponse
    {
        $gatewayEnum = PaymentGatewayEnum::tryFrom($gateway);

        if (!$gatewayEnum || !$gatewayEnum->isExternal()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'درگاه پرداخت نامعتبر است.');
        }

        $gatewayReference = $this->resolveGatewayReference($request);

        if (!$gatewayReference) {
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
                ->with('error', 'پرداخت مورد نظر یافت نشد.');
        }

        try {
            if (!$payment->isPaid()) {
                $payment = $verifyPaymentAction->execute(
                    payment: $payment,
                    callbackPayload: $request->all(),
                );
            }

            if ($payment->isPaid()) {
                $completePaidPaymentAction->execute($payment);
            }
        } catch (Throwable $exception) {
            report($exception);

            $payment->refresh();
        }

        return redirect()->route('payment.result', [
            'paymentToken' => $payment->public_token,
        ]);
    }

    private function resolveGatewayReference(Request $request): ?string
    {
        return $request->input('trackId')
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
    }
}
