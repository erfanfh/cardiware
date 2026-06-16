<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\ResolveDefaultPaymentGatewayAction;
use App\Application\Financial\Actions\Payment\StartPaymentAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PaymentRetryController extends Controller
{
    public function __invoke(
        string                             $paymentToken,
        StartPaymentAction                 $startPaymentAction,
        ResolveDefaultPaymentGatewayAction $resolveDefaultPaymentGatewayAction,
    ): RedirectResponse
    {
        $payment = Payment::query()
            ->where('public_token', $paymentToken)
            ->firstOrFail();

        $order = $payment->payable;

        if (!$order instanceof Order) {
            abort(404);
        }

        if ((int)$order->user_id !== (int)Auth::id()) {
            abort(404);
        }

        if ($payment->isPaid()) {
            return redirect()
                ->route('payment.result', ['paymentToken' => $payment->public_token])
                ->with('success', 'این سفارش قبلا با موفقیت پرداخت شده است.');
        }

        try {
            $gateway = $resolveDefaultPaymentGatewayAction->execute();

            $newPayment = $startPaymentAction->execute(
                payable: $order,
                gateway: $gateway,
                callbackUrl: route('payment.callback', [
                    'gateway' => $gateway->value,
                ]),
                metadata: [
                    'source' => 'retry',
                    'previous_payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'description' => 'پرداخت مجدد سفارش ' . $order->order_number,
                    'mobile' => $order->user?->mobile,
                ],
            );

            $redirectUrl = data_get($newPayment->request_payload, 'redirect_url');

            if (!$redirectUrl) {
                return redirect()
                    ->route('payment.result', ['paymentToken' => $newPayment->public_token])
                    ->with('error', 'امکان اتصال مجدد به درگاه پرداخت وجود ندارد.');
            }

            return redirect()->away($redirectUrl);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('payment.result', ['paymentToken' => $payment->public_token])
                ->with('error', 'خطایی در تلاش مجدد برای پرداخت رخ داد.');
        }
    }
}
