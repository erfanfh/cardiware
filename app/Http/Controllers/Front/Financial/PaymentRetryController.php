<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\StartPaymentAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PaymentRetryController extends Controller
{
    public function __invoke(Payment $payment, StartPaymentAction $startPaymentAction): RedirectResponse
    {
        $order = $payment->payable;

        if (!$order instanceof Order) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'سفارش مربوط به پرداخت یافت نشد.');
        }

        try {
            $gateway = PaymentGatewayEnum::ZIBAL;

            $newPayment = $startPaymentAction->execute(
                payable: $order,
                gateway: $gateway,
                callbackUrl: route('payment.callback', [
                    'gateway' => $gateway->value,
                ]),
                metadata: [
                    'source' => 'retry',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'description' => 'پرداخت مجدد سفارش ' . $order->order_number,
                    'mobile' => $order->user?->mobile,
                ],
            );

            $redirectUrl = data_get($newPayment->request_payload, 'redirect_url');

            if (!$redirectUrl) {
                return redirect()
                    ->route('payment.failed', ['payment' => $newPayment->tracking_code])
                    ->with('error', 'امکان اتصال مجدد به درگاه پرداخت وجود ندارد.');
            }

            return redirect()->away($redirectUrl);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('payment.failed', ['payment' => $payment->tracking_code])
                ->with('error', 'خطایی در تلاش مجدد برای پرداخت رخ داد.');
        }
    }
}
