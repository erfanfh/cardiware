<?php

namespace App\Application\Loyalty\Listeners\Raffle;

use App\Application\Loyalty\Actions\Reward\RefundRaffleTicketRewardRedemptionAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Events\DrawTicketsInvalidatedEvent;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class RefundInvalidatedRaffleRewardGrantListener implements ShouldQueue
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
        DrawTicketsInvalidatedEvent $event,
        RefundRaffleTicketRewardRedemptionAction $refund,
    ): void {
        if (!$event->ticketGrantId) {
            return;
        }

        $grant = TicketGrant::query()->find($event->ticketGrantId);

        // Targeted single-ticket invalidations do not refund the whole reward.
        if (!$grant || !$grant->isInvalidated()) {
            return;
        }

        $redemptionMorph = (new RewardRedemption())->getMorphClass();

        if ($grant->source_type !== $redemptionMorph || !$grant->source_id) {
            return;
        }

        $redemption = RewardRedemption::query()->find($grant->source_id);
        if (!$redemption) {
            return;
        }

        $actor = $event->actorUserId
            ? User::query()->find($event->actorUserId)
            : null;

        $refund->execute(
            redemption: $redemption,
            reason: 'بازگشت امتیاز به دلیل ابطال کامل بلیط های پاداش: ' . $event->reason,
            actor: $actor,
        );
    }
}
