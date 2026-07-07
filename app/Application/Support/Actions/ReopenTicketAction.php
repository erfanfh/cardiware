<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;

class ReopenTicketAction
{
    public function handle(Ticket $ticket): Ticket
    {
        if ($ticket->status !== TicketStatusEnum::CLOSED) {
            return $ticket;
        }

        $ticket->forceFill([
            'status' => TicketStatusEnum::OPEN,
            'closed_at' => null,
            'closed_by_user_id' => null,
        ])->save();

        return $ticket->refresh();
    }
}
