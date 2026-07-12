<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CloseInactiveAnsweredTicketsAction
{
    private const INACTIVITY_DAYS = 7;

    private const CHUNK_SIZE = 200;

    /**
     * Close answered tickets that have had no new messages
     * for at least seven days.
     */
    public function execute(): int
    {
        $inactiveBefore = now()->subDays(self::INACTIVITY_DAYS);

        $closedTicketsCount = 0;

        Ticket::query()
            ->select('id')
            ->where('status', TicketStatusEnum::ANSWERED)
            ->whereNotNull('last_message_at')
            ->where('last_message_at', '<=', $inactiveBefore)
            ->chunkById(
                self::CHUNK_SIZE,
                function ($tickets) use (
                    $inactiveBefore,
                    &$closedTicketsCount
                ): void {
                    foreach ($tickets as $candidate) {
                        $wasClosed = $this->closeTicketIfStillEligible(
                            ticketId: $candidate->getKey(),
                            inactiveBefore: $inactiveBefore,
                        );

                        if ($wasClosed) {
                            $closedTicketsCount++;
                        }
                    }
                }
            );

        return $closedTicketsCount;
    }

    private function closeTicketIfStillEligible(
        int             $ticketId,
        CarbonInterface $inactiveBefore,
    ): bool
    {
        return DB::transaction(function () use (
            $ticketId,
            $inactiveBefore
        ): bool {
            /** @var Ticket|null $ticket */
            $ticket = Ticket::query()
                ->lockForUpdate()
                ->find($ticketId);

            if ($ticket === null) {
                return false;
            }

            if ($ticket->status !== TicketStatusEnum::ANSWERED) {
                return false;
            }

            if (
                $ticket->last_message_at === null ||
                $ticket->last_message_at->gt($inactiveBefore)
            ) {
                return false;
            }

            $ticket->status = TicketStatusEnum::CLOSED;
            $ticket->closed_at = now();

            $ticket->save();

            return true;
        });
    }
}
