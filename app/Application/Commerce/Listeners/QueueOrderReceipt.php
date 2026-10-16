<?php

namespace App\Application\Commerce\Listeners;

use App\Application\Commerce\Jobs\SendOrderReceiptJob;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

final class QueueOrderReceipt
{
    public function handle(OrderPaidEvent $event): void
    {
        DB::afterCommit(fn () => $this->queue($event->orderId));
    }

    private function queue(int $orderId): void
    {
        try {
            $order = Order::query()->with('user')->find($orderId);
            if (! $order || ! $order->isPaid() || $order->receipt_email_sent_at
                || ! filter_var(trim((string) $order->user?->email), FILTER_VALIDATE_EMAIL)) {
                return;
            }

            SendOrderReceiptJob::dispatch($orderId)->afterCommit();
        } catch (Throwable $exception) {
            // Queue or notification failures must not fail an already committed payment.
            report($exception);
        }
    }
}
