<?php

namespace App\Application\Commerce\Jobs;

use App\Application\Commerce\DTOs\OrderReceiptData;
use App\Application\Commerce\Mails\OrderReceiptMail;
use App\Domain\Commerce\Models\Order;
use App\Infrastructure\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class SendOrderReceiptJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 600;

    public function __construct(public readonly int $orderId)
    {
        $connection = config('mail.order_receipts.connection') ?: config('queue.default');

        // A synchronous development/test queue must never send mail in the payment request.
        if (in_array(config("queue.connections.{$connection}.driver"), ['sync', 'null'], true)) {
            $connection = 'database';
        }

        $this->onConnection($connection);
    }

    public function uniqueId(): string
    {
        return 'order-receipt:'.$this->orderId;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->uniqueId()))->releaseAfter(10)->expireAfter(60)];
    }

    public function handle(EmailService $email): void
    {
        $order = Order::query()->with('user')->find($this->orderId);
        if (! $order || ! $order->isPaid() || $order->receipt_email_sent_at) {
            return;
        }

        $recipient = trim((string) $order->user?->email);
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $message = $email->send($recipient, new OrderReceiptMail(OrderReceiptData::fromOrder($order)));
        if ($message === null) {
            throw new RuntimeException('Order receipt mail was not accepted for sending.');
        }

        // Delivery bookkeeping must not modify business timestamps or trigger order observers.
        DB::table('orders')->where('id', $this->orderId)->update([
            'receipt_email_sent_at' => now(),
            'receipt_email_message_id' => $message->getMessageId(),
        ]);
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }
}
