<?php

namespace App\Http\Controllers\Front\Financial;

use App\Application\Financial\Actions\Payment\FinalizeVerifiedPaymentAction;
use App\Application\Financial\Actions\Payment\VerifyPaymentAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Domain\Launcher\Models\LauncherCheckoutSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Throwable;

class PaymentResultController extends Controller
{
    public function __invoke(
        string                        $paymentToken,
        VerifyPaymentAction           $verifyPaymentAction,
        FinalizeVerifiedPaymentAction $finalizeVerifiedPaymentAction,
        Request                       $request,
    ): View|RedirectResponse
    {
        $payment = Payment::query()
            ->where('public_token', $paymentToken)
            ->with('payable')
            ->firstOrFail();

        /*
         * Ownership must be checked before running any side effect.
         */
        $this->ensureCurrentUserOwnsPayable(
            $payment->payable,
        );

        /*
         * Immediate self-healing verification.
         *
         * No scheduler is involved.
         * If the callback verification had a timeout or temporary failure,
         * the authenticated result page retries it immediately.
         */
        if (
            $payment->canBeVerified()
            && $payment->gateway->isExternal()
        ) {
            try {
                $payment = $verifyPaymentAction->execute(
                    payment: $payment,
                    callbackPayload: $payment->callback_payload ?? [],
                );
            } catch (Throwable $exception) {
                report($exception);

                $payment->refresh();
            }
        }

        /*
         * Idempotent self-healing.
         *
         * This handles both:
         *
         * - successful Payment whose payable was not completed
         * - failed Payment whose Hold or coupon was not released
         */
        try {
            $finalizeVerifiedPaymentAction->execute($payment);
        } catch (Throwable $exception) {
            report($exception);
        }

        /*
         * Reload the latest state after finalization.
         */
        $payment = Payment::query()
            ->whereKey($payment->id)
            ->with('payable')
            ->firstOrFail();

        $payable = $payment->payable;

        if ($payable instanceof Order && $payable->isPaid()) {
            $launcherRedirect = $this->launcherRedirect($request, $payable);

            if ($launcherRedirect instanceof RedirectResponse) {
                return $launcherRedirect;
            }
        }

        return match (true) {
            $payable instanceof Order => $this->renderOrderResult(
                payment: $payment,
                order: $payable,
            ),

            $payable instanceof WalletTopUp => $this->renderWalletTopUpResult(
                payment: $payment,
                topUp: $payable,
            ),

            default => abort(404),
        };
    }

    private function launcherRedirect(
        Request $request,
        Order $order,
    ): ?RedirectResponse {
        $state = $request->session()->pull('launcher_checkout_state');

        if (!is_string($state) || $state === '') {
            return null;
        }

        $checkout = LauncherCheckoutSession::query()
            ->where('state_hash', hash('sha256', $state))
            ->where('user_id', $order->user_id)
            ->whereNull('consumed_at')
            ->first();

        if (!$checkout || $checkout->expires_at->isPast()) {
            return null;
        }

        $ownsSaleItem = $order->items()
            ->where('purchasable_type', 'product_sale_item')
            ->where('purchasable_id', $checkout->product_sale_item_id)
            ->exists();

        if (!$ownsSaleItem) {
            return null;
        }

        $checkout->forceFill(['order_id' => $order->getKey()])->save();

        return redirect()->to(
            URL::temporarySignedRoute(
                'launcher.purchase-complete',
                $checkout->expires_at,
                ['state' => $state],
            )
        );
    }

    /**
     * Ensure the authenticated user owns the payment payable.
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
            $payable instanceof Order => (int)$payable->user_id === $userId,
            $payable instanceof WalletTopUp => (int)$payable->wallet()->value('user_id') === $userId,
            default => false,
        };

        if (!$ownsPayable) {
            abort(404);
        }
    }

    /**
     * Render an order payment receipt.
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
     * Render a wallet top-up result.
     *
     * A completed order-shortfall top-up eventually redirects to the
     * regular order receipt by using its internal WALLET payment.
     */
    private function renderWalletTopUpResult(
        Payment     $payment,
        WalletTopUp $topUp,
    ): View
    {
        $topUp->load([
            'wallet',
            'creditedTransaction',

            /*
             * These relations only have values for top-ups created
             * to complete an order payment.
             */
            'order',
            'orderPayment',
            'walletHold',
        ]);

        $wallet = $topUp->wallet;

        if (!$wallet) {
            abort(404);
        }

        $wallet->refresh();

        /*
         * Successful mixed payment:
         *
         * External Payment paid only the shortage.
         * Internal WALLET Payment paid the complete Order.
         *
         * Therefore the user should see the normal order receipt using
         * the internal payment, not a simple wallet top-up receipt.
         */
        if (
            $topUp->isOrderPayment()
            && $topUp->isCompleted()
            && $topUp->order
            && $topUp->orderPayment
            && $topUp->order->isPaid()
            && $topUp->orderPayment->isPaid()
        ) {
            return $this->renderOrderResult(
                payment: $topUp->orderPayment,
                order: $topUp->order,
            );
        }

        $isCredited = $payment->isPaid()
            && $topUp->isCompleted()
            && $topUp->credited_transaction_id !== null;

        /*
         * For an order shortfall, success requires both wallet credit
         * and successful order payment.
         */
        $isCompletedOrderPayment = $topUp->isOrderPayment()
            && $isCredited
            && $topUp->order_payment_id !== null
            && $topUp->order?->isPaid();

        $resultState = match (true) {
            $topUp->isManual() && $isCredited => 'success',
            $isCompletedOrderPayment => 'success',
            /*
             * External payment is paid, but internal credit/order
             * completion still needs recovery.
             */
            $payment->isPaid() => 'processing',
            $this->isUnsuccessfulTerminalPayment($payment) => 'failed',
            default => 'pending',
        };

        return view(
            'front.financial.payments.results.wallet-top-up',
            [
                'payment' => $payment,
                'topUp' => $topUp,
                'wallet' => $wallet,
                'resultState' => $resultState,

                /*
                 * These values allow the Blade to show better text for
                 * shortfall payments later without changing this controller.
                 */
                'isOrderPaymentTopUp' => $topUp->isOrderPayment(),

                'relatedOrder' => $topUp->order,
            ],
        );
    }

    private function isUnsuccessfulTerminalPayment(Payment $payment): bool
    {
        return in_array(
            $payment->status,
            [
                PaymentStatusEnum::FAILED,
                PaymentStatusEnum::CANCELLED,
                PaymentStatusEnum::EXPIRED,
            ],
            true,
        );
    }
}
