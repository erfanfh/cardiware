<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartCreatedPaymentAction
{
    public function __construct(
        private readonly MarkPaymentAsPendingAction $markPaymentAsPendingAction,
        private readonly MarkPaymentAsFailedAction  $markPaymentAsFailedAction,
        private readonly PaymentGatewayManager      $paymentGatewayManager,
    )
    {
    }

    /**
     * Start an already-created payment through an external gateway.
     *
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        Payment $payment,
        string  $callbackUrl,
        array   $metadata = [],
    ): Payment
    {
        $lock = Cache::lock(
            name: 'payment:start:' . $payment->id,
            seconds: 30,
        );

        try {
            return $lock->block(
                seconds: 5,
                callback: function () use (
                    $payment,
                    $callbackUrl,
                    $metadata,
                ): Payment {
                    $payment = Payment::query()
                        ->findOrFail($payment->id);

                    /*
                     * اگر قبلاً شروع شده، همان Payment را برگردان.
                     */
                    if ($payment->status !== PaymentStatusEnum::INITIATED) {
                        return $payment;
                    }

                    if (!$payment->gateway->isExternal()) {
                        throw new DomainException(
                            'Only external payments can be started through a gateway.'
                        );
                    }

                    /*
                     * تماس HTTP خارج از transaction دیتابیس انجام می‌شود.
                     */
                    $gatewayDriver = $this->paymentGatewayManager->get(
                        $payment->gateway,
                    );

                    $result = $gatewayDriver->start(
                        new StartPaymentRequestDTO(
                            payment: $payment,
                            callbackUrl: $callbackUrl,
                            metadata: $metadata,
                        ),
                    );

                    return DB::transaction(function () use (
                        $payment,
                        $result,
                    ): Payment {
                        $payment = Payment::query()
                            ->whereKey($payment->id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        /*
                         * ممکن است پردازش دیگری زودتر نتیجه را ثبت کرده باشد.
                         */
                        if ($payment->status !== PaymentStatusEnum::INITIATED) {
                            return $payment;
                        }

                        if (!$result->successful) {
                            return $this->markPaymentAsFailedAction->execute(
                                payment: $payment,
                                gatewayStatus: $result->gatewayStatus,
                                gatewayMessage: $result->gatewayMessage,
                                payload: $result->rawResponse,
                            );
                        }

                        return $this->markPaymentAsPendingAction->execute(
                            payment: $payment,
                            gatewayReference: $result->gatewayReference,
                            gatewayStatus: $result->gatewayStatus,
                            gatewayMessage: $result->gatewayMessage,
                            requestPayload: [
                                'redirect_url' => $result->redirectUrl,
                                'gateway_response' => $result->rawResponse,
                            ],
                        );
                    }, 3);
                },
            );
        } catch (LockTimeoutException $exception) {
            /*
             * درخواست دیگری در حال شروع همین پرداخت است.
             * بعد از آزادشدن قفل، آخرین وضعیت را برمی‌گردانیم.
             */
            return Payment::query()->findOrFail($payment->id);
        } finally {
            optional($lock)->release();
        }
    }
}
