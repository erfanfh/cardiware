<?php

namespace App\Providers;

use App\Application\Loyalty\Listeners\Raffle\RefundCancelledRaffleRewardRedemptionsListener;
use App\Application\Loyalty\Listeners\Raffle\RefundInvalidatedRaffleRewardGrantListener;
use App\Domain\Raffle\Events\DrawCampaignCancelledEvent;
use App\Domain\Raffle\Events\DrawTicketsInvalidatedEvent;
use App\Application\Loyalty\Actions\EvaluateRewardOfferAvailabilityAction;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class LoyaltyRaffleIntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Integration bindings are resolved through Laravel's container.
    }

    public function boot(): void
    {
        Event::listen(
            DrawCampaignCancelledEvent::class,
            RefundCancelledRaffleRewardRedemptionsListener::class,
        );

        Event::listen(
            DrawTicketsInvalidatedEvent::class,
            RefundInvalidatedRaffleRewardGrantListener::class,
        );


        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule
                ->command('loyalty:reconcile-raffle-ticket-refunds --limit=5000')
                ->everyThirtyMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });

        View::composer('front.raffles.show', function ($view): void {
            /** @var DrawCampaign|null $campaign */
            $campaign = $view->getData()['campaign'] ?? null;

            if (!$campaign instanceof DrawCampaign) {
                return;
            }

            $campaignMorph = $campaign->getMorphClass();
            $offers = RewardOffer::query()
                ->currentlyActive()
                ->where('type', RewardTypeEnum::RAFFLE_TICKET->value)
                ->where('target_type', $campaignMorph)
                ->where('target_id', $campaign->getKey())
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $user = Auth::user();
            $availabilityAction = app(EvaluateRewardOfferAvailabilityAction::class);

            $loyaltyRaffleRewardOffers = $offers->map(fn(RewardOffer $offer) => [
                'offer' => $offer,
                'availability' => $user
                    ? $availabilityAction->execute($offer, $user)
                    : null,
            ]);

            $view->with('loyaltyRaffleRewardOffers', $loyaltyRaffleRewardOffers);
        });
    }
}
