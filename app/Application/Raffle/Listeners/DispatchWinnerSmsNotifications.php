<?php

namespace App\Application\Raffle\Listeners;

use App\Application\Raffle\Jobs\SendRaffleWinnerSmsJob;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawReservePromotedEvent;
use App\Domain\Raffle\Events\DrawWinnersSelectedEvent;
use App\Domain\Raffle\Models\DrawWinner;

class DispatchWinnerSmsNotifications
{
    public function handle(DrawWinnersSelectedEvent|DrawReservePromotedEvent $event): void
    {
        $winnerIds = $event instanceof DrawReservePromotedEvent
            ? [$event->winnerId]
            : DrawWinner::query()
                ->whereIn('id', $event->winnerIds)
                ->where('kind', DrawWinnerKindEnum::PRIMARY->value)
                ->where('status', DrawWinnerStatusEnum::PENDING->value)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        foreach ($winnerIds as $winnerId) {
            SendRaffleWinnerSmsJob::dispatch($winnerId, 'winner');
        }
    }
}
