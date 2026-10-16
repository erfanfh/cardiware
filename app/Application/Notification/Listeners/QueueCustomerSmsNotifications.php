<?php

namespace App\Application\Notification\Listeners;

use App\Application\Notification\Jobs\SendCustomerSmsJob;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Notification\Enums\CustomerSmsType;
use App\Domain\Support\Events\TicketRepliedEvent;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use Throwable;

final class QueueCustomerSmsNotifications
{
    public function orderPaid(OrderPaidEvent $event): void
    {
        $this->dispatch(CustomerSmsType::ORDER_PAID, $event->orderId);
    }

    public function ticketReplied(TicketRepliedEvent $event): void
    {
        $this->dispatch(CustomerSmsType::TICKET_REPLY, $event->messageId);
    }

    private function dispatch(CustomerSmsType $type, int $subjectId): void
    {
        try {
            if (! app(NotificationsSettings::class)->get($type->settingKey(), false)) {
                return;
            }

            SendCustomerSmsJob::dispatch($type, $subjectId)->afterCommit();
        } catch (Throwable $exception) {
            // A notification outage must not fail a committed payment or support reply.
            report($exception);
        }
    }
}
