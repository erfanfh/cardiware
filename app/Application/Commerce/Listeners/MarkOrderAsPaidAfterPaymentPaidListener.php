<?php

namespace App\Application\Commerce\Listeners;

use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Financial\Events\PaymentPaid;
use App\Domain\Commerce\Models\Order;
use Throwable;

class MarkOrderAsPaidAfterPaymentPaidListener
{
    public function __construct(
        private readonly MarkOrderAsPaidAction $markOrderAsPaidAction,
    )
    {
    }

    /**
     * Handle the event.
     *
     * @param PaymentPaid $event
     * @return void
     * @throws Throwable
     */
    public function handle(PaymentPaid $event): void
    {
        $payable = $event->payment->payable;

        if (!$payable instanceof Order) {
            return;
        }

        $this->markOrderAsPaidAction->execute($payable);
    }
}
