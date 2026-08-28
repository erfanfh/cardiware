<?php

namespace App\Console\Commands;

use App\Application\Raffle\Actions\Winner\ExpireDrawWinnerAction;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Console\Command;
use Throwable;

class ExpireOverdueDrawWinnersCommand extends Command
{
    protected $signature = 'raffles:expire-overdue-winners';
    protected $description = 'Expire overdue pending raffle winners and promote reserves when available.';

    public function handle(ExpireDrawWinnerAction $expire): int
    {
        DrawWinner::query()
            ->where('status', DrawWinnerStatusEnum::PENDING)
            ->whereNotNull('confirmation_deadline_at')
            ->where('confirmation_deadline_at', '<=', now())
            ->where(function ($query): void {
                $query->where('kind', DrawWinnerKindEnum::PRIMARY)
                    ->orWhereNotNull('promoted_at');
            })
            ->whereDoesntHave('replacement')
            ->orderBy('id')
            ->chunkById(100, function ($winners) use ($expire): void {
                foreach ($winners as $winner) {
                    try {
                        $expire->execute($winner);
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            });

        return self::SUCCESS;
    }
}
