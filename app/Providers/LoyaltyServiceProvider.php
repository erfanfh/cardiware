<?php

namespace App\Providers;

use App\Application\Loyalty\Listeners\AwardCommentPointsListener;
use App\Application\Loyalty\Listeners\AwardPurchasePointsListener;
use App\Application\Loyalty\Listeners\AwardRegistrationPointsListener;
use App\Application\Loyalty\Listeners\ReverseCommentPointsListener;
use App\Application\Loyalty\Listeners\ReversePurchasePointsListener;
use App\Application\Loyalty\Listeners\RecordRegistrationPointEligibilityListener;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Events\OrderPaymentRevokedEvent;
use App\Domain\Discussion\Events\CommentApprovedEvent;
use App\Domain\Discussion\Events\CommentDisqualifiedEvent;
use App\Domain\Identity\Events\UserRegisteredEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LoyaltyServiceProvider extends ServiceProvider
{
    public function boot(Schedule $schedule): void
    {
        Event::listen(
            UserRegisteredEvent::class,
            RecordRegistrationPointEligibilityListener::class,
        );

        Event::listen(
            UserRegisteredEvent::class,
            AwardRegistrationPointsListener::class,
        );

        Event::listen(
            OrderPaidEvent::class,
            AwardPurchasePointsListener::class,
        );

        Event::listen(
            OrderPaymentRevokedEvent::class,
            ReversePurchasePointsListener::class,
        );

        Event::listen(
            CommentApprovedEvent::class,
            AwardCommentPointsListener::class,
        );

        Event::listen(
            CommentDisqualifiedEvent::class,
            ReverseCommentPointsListener::class,
        );

        $schedule->command('loyalty:award-membership-anniversaries --lookback=7')
            ->dailyAt('00:15')
            ->withoutOverlapping(120)
            ->onOneServer();

        // Fast safety net for transient queue/listener failures.
        $schedule->command('loyalty:reconcile-core-points --hours=3 --limit=5000')
            ->hourlyAt(25)
            ->withoutOverlapping(30)
            ->onOneServer();

        // Wider daily pass covers longer outages without scanning historical data forever.
        $schedule->command('loyalty:reconcile-core-points --hours=72 --limit=20000')
            ->dailyAt('02:45')
            ->withoutOverlapping(120)
            ->onOneServer();
    }
}
