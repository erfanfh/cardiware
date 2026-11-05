<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\Actions\Ticket\InvalidateTicketGrantAction;
use App\Application\Raffle\DTOs\InvalidateTicketGrantDTO;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\TicketGrant;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Throwable;

/**
 * کد قرعه‌کشی باشگاه با خرید: به ازای هر «purchase_raffle_toman_per_ticket» تومان خرید
 * یک کد در قرعه‌کشی فعال باشگاه (club_raffle_campaign_id). خرید بیشتر = شانس بیشتر.
 * با لغو/مرجوعی سفارش، کدهای همان سفارش باطل می‌شوند.
 */
class IssuePurchaseRaffleTicketsListener
{
    public function __construct(private readonly LoyaltySettings $settings)
    {
    }

    public function handlePaid(OrderPaidEvent $event): void
    {
        try {
            $step = (int) $this->settings->get('purchase_raffle_toman_per_ticket', 0);
            $campaign = DrawCampaign::query()->find((int) $this->settings->get('club_raffle_campaign_id', 0));
            $order = Order::query()->with('user')->find($event->orderId);

            if ($step <= 0 || $campaign === null || $order?->user === null || ! $campaign->acceptsTicketsAt(now())) {
                return;
            }

            $quantity = intdiv(max(0, (int) $order->total_amount), $step);
            if ($quantity <= 0) {
                return;
            }

            app(IssueDrawTicketsAction::class)->execute(new IssueDrawTicketsDTO(
                campaign: $campaign,
                user: $order->user,
                quantity: $quantity,
                idempotencyKey: 'purchase-raffle:' . $order->getKey(),
                source: $order,
                metadata: ['source' => 'purchase', 'order_number' => $order->order_number],
            ));
        } catch (Throwable $exception) {
            // سقف کد یا بسته‌شدن قرعه‌کشی نباید پرداخت را تحت تأثیر قرار دهد.
            report($exception);
        }
    }

    public function handleRevoked(OrderPaymentRevokedEvent $event): void
    {
        try {
            $order = Order::query()->find($event->orderId);
            if ($order === null) {
                return;
            }

            TicketGrant::query()
                ->where('source_type', $order->getMorphClass())
                ->where('source_id', $order->getKey())
                ->whereNull('invalidated_at')
                ->get()
                ->each(fn (TicketGrant $grant) => app(InvalidateTicketGrantAction::class)->execute(
                    new InvalidateTicketGrantDTO(grant: $grant, reason: 'سفارش لغو یا مرجوع شد'),
                ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
