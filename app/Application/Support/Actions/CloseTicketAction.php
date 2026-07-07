<?php

namespace App\Application\Support\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;

class CloseTicketAction
{
    public function handle(Ticket $ticket, User $user): Ticket
    {
        if ($ticket->status === TicketStatusEnum::CLOSED) {
            return $ticket;
        }

        $ticket->forceFill([
            'status' => TicketStatusEnum::CLOSED,
            'closed_at' => now(),
            'closed_by_user_id' => $user->id,
        ])->save();

        return $ticket->refresh();
    }
}
