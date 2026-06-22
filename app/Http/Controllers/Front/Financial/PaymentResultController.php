<?php

namespace App\Http\Controllers\Front\Financial;

use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PaymentResultController extends Controller
{
    public function __invoke(string $paymentToken): View|RedirectResponse
    {
        $payment = Payment::query()
            ->where('public_token', $paymentToken)
            ->with([
                'payable.rootItems.children',
                'payable.rootItems.purchasable',
                'payable.rootItems.children.purchasable',
                'payable.rootItems.purchasable.product.picture.primaryVariant',
                'payable.rootItems.purchasable.product.picture.variants',
                'payable.rootItems.children.purchasable.product.picture.primaryVariant',
                'payable.rootItems.children.purchasable.product.picture.variants',
            ])
            ->firstOrFail();

        $order = $payment->payable;

        if (!$order instanceof Order) {
            abort(404);
        }

        if ((int)$order->user_id !== (int)Auth::id()) {
            abort(404);
        }

        return view('front.financial.payments.result', [
            'payment' => $payment,
            'order' => $order,
            'isPaid' => $payment->isPaid(),
        ]);
    }
}
