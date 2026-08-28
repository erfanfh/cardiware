<?php

namespace App\Application\Loyalty\Listeners\Raffle;

use App\Application\Loyalty\Actions\Reward\RefundRaffleTicketRewardRedemptionAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Events\DrawCampaignCancelledEvent;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class RefundCancelledRaffleRewardRedemptionsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';
    public int $tries = 5;

    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    /** @throws Throwable */
    public function handle(
        DrawCampaignCancelledEvent $event,
        RefundRaffleTicketRewardRedemptionAction $refund,
    ): void {
        $redemptionMorph = (new RewardRedemption())->getMorphClass();
        $actor = $event->actorUserId
            ? User::query()->find($event->actorUserId)
            : null;

        TicketGrant::query()
            ->where('campaign_id', $event->campaignId)
            ->where('source_type', $redemptionMorph)
            ->whereNotNull('source_id')
            ->orderBy('id')
            ->chunkById(200, function ($grants) use ($refund, $actor, $event): void {
                $redemptionIds = $grants
                    ->pluck('source_id')
                    ->filter()
                    ->unique()
                    ->values();

                RewardRedemption::query()
                    ->whereIn('id', $redemptionIds)
                    ->get()
                    ->each(function (RewardRedemption $redemption) use ($refund, $actor, $event): void {
                        $refund->execute(
                            redemption: $redemption,
                            reason: 'بازگشت امتیاز به دلیل لغو قرعه کشی'
                                . ($event->reason ? ': ' . $event->reason : ''),
                            actor: $actor,
                        );
                    });
            });
    }
}
