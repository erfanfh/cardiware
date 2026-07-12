<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\CompletePaidPaymentAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PaymentResultController extends Controller
{
    public function __invoke(
        string                    $paymentToken,
        CompletePaidPaymentAction $completePaidPaymentAction,
    ): View
    {
        $payment = Payment::query()
            ->where('public_token', $paymentToken)
            ->with('payable')
            ->firstOrFail();

        $this->ensureCurrentUserOwnsPayable(
            $payment->payable,
        );

        /*
         * Self-healing:
         *
         * ممکن است پرداخت در درگاه موفق شده باشد، اما اجرای side effect
         * مربوط به سفارش یا شارژ کیف پول در callback با خطای موقت مواجه
         * شده باشد. از آنجا که تکمیل پرداخت‌ها idempotent است، اینجا یک‌بار
         * دیگر تلاش می‌کنیم.
         */
        if ($payment->isPaid()) {
            try {
                $completePaidPaymentAction->execute($payment);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        /*
         * پس از تلاش برای تکمیل عملیات، اطلاعات را دوباره از دیتابیس
         * دریافت می‌کنیم تا وضعیت نهایی WalletTopUp یا Order نمایش داده شود.
         */
        $payment = Payment::query()
            ->whereKey($payment->id)
            ->with('payable')
            ->firstOrFail();

        $payable = $payment->payable;

        return match (true) {
            $payable instanceof Order =>
            $this->renderOrderResult(
                payment: $payment,
                order: $payable,
            ),

            $payable instanceof WalletTopUp =>
            $this->renderWalletTopUpResult(
                payment: $payment,
                topUp: $payable,
            ),

            default => abort(404),
        };
    }

    /**
     * Ensure the authenticated user owns the payment's payable model.
     */
    private function ensureCurrentUserOwnsPayable(
        ?Model $payable,
    ): void
    {
        if (!$payable) {
            abort(404);
        }

        $userId = (int)Auth::id();

        if ($userId <= 0) {
            abort(404);
        }

        $ownsPayable = match (true) {
            $payable instanceof Order =>
                (int)$payable->user_id === $userId,

            $payable instanceof WalletTopUp =>
                (int)$payable->wallet()
                    ->value('user_id') === $userId,

            default => false,
        };

        if (!$ownsPayable) {
            abort(404);
        }
    }

    /**
     * Render order payment result.
     */
    private function renderOrderResult(
        Payment $payment,
        Order   $order,
    ): View
    {
        $order->load([
            'rootItems.children',
            'rootItems.purchasable',
            'rootItems.children.purchasable',

            'rootItems.purchasable.product.picture.primaryVariant',
            'rootItems.purchasable.product.picture.variants',

            'rootItems.children.purchasable.product.picture.primaryVariant',
            'rootItems.children.purchasable.product.picture.variants',
        ]);

        return view(
            'front.financial.payments.results.order',
            [
                'payment' => $payment,
                'order' => $order,
                'isPaid' => $payment->isPaid(),
            ],
        );
    }

    /**
     * Render wallet top-up payment result.
     */
    private function renderWalletTopUpResult(
        Payment     $payment,
        WalletTopUp $topUp,
    ): View
    {
        $topUp->load([
            'wallet',
            'creditedTransaction',
        ]);

        $wallet = $topUp->wallet->refresh();

        $isCredited = $payment->isPaid()
            && $topUp->isCompleted()
            && $topUp->credited_transaction_id !== null;

        $resultState = match (true) {
            $isCredited =>
            'success',

            $payment->isPaid() =>
            'processing',

            $payment->status->isFailed() =>
            'failed',

            default =>
            'pending',
        };

        return view(
            'front.financial.payments.results.wallet-top-up',
            [
                'payment' => $payment,
                'topUp' => $topUp,
                'wallet' => $wallet,
                'resultState' => $resultState,
            ],
        );
    }
}
