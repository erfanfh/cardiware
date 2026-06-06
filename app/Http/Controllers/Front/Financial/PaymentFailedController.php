<?php

namespace App\Http\Controllers\Front\Financial;

use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentFailedController extends Controller
{
    public function __invoke(Payment $payment): View|RedirectResponse
    {
        $payment->load([
            'payable.rootItems.children',
            'payable.rootItems.purchasable',
            'payable.rootItems.children.purchasable',
        ]);

        $order = $payment->payable;

        if (!$order instanceof Order) {
            return redirect()->route('cart.index');
        }

        return view('front.financial.payments.failed', [
            'payment' => $payment,
            'order' => $order,
        ]);
    }
}
