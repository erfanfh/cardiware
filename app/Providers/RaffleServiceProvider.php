<?php

namespace App\Providers;

use App\Application\Raffle\Listeners\DispatchWinnerSmsNotifications;
use App\Application\Raffle\Listeners\RecordRaffleAuditEvent;
use App\Domain\Raffle\Events\DrawCampaignCancelledEvent;
use App\Domain\Raffle\Events\DrawCampaignCreatedEvent;
use App\Domain\Raffle\Events\DrawCampaignStatusChangedEvent;
use App\Domain\Raffle\Events\DrawCampaignUpdatedEvent;
use App\Domain\Raffle\Events\DrawCompletedEvent;
use App\Domain\Raffle\Events\DrawFailedEvent;
use App\Domain\Raffle\Events\DrawPrizeDeliveredEvent;
use App\Domain\Raffle\Events\DrawRedrawStartedEvent;
use App\Domain\Raffle\Events\DrawReservePromotedEvent;
use App\Domain\Raffle\Events\DrawStartedEvent;
use App\Domain\Raffle\Events\DrawTicketsInvalidatedEvent;
use App\Domain\Raffle\Events\DrawTicketsIssuedEvent;
use App\Domain\Raffle\Events\DrawWinnerConfirmedEvent;
use App\Domain\Raffle\Events\DrawWinnerExpiredEvent;
use App\Domain\Raffle\Events\DrawWinnerRejectedEvent;
use App\Domain\Raffle\Events\DrawWinnersSelectedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class RaffleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(DrawWinnersSelectedEvent::class, DispatchWinnerSmsNotifications::class);
        Event::listen(DrawReservePromotedEvent::class, DispatchWinnerSmsNotifications::class);

        foreach ([
            DrawCampaignCancelledEvent::class,
            DrawCampaignCreatedEvent::class,
            DrawCampaignUpdatedEvent::class,
            DrawCampaignStatusChangedEvent::class,
            DrawCompletedEvent::class,
            DrawFailedEvent::class,
            DrawPrizeDeliveredEvent::class,
            DrawRedrawStartedEvent::class,
            DrawReservePromotedEvent::class,
            DrawStartedEvent::class,
            DrawTicketsInvalidatedEvent::class,
            DrawTicketsIssuedEvent::class,
            DrawWinnerConfirmedEvent::class,
            DrawWinnerExpiredEvent::class,
            DrawWinnerRejectedEvent::class,
            DrawWinnersSelectedEvent::class,
        ] as $eventClass) {
            Event::listen($eventClass, RecordRaffleAuditEvent::class);
        }
    }
}
