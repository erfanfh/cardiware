<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Models\Ticket;

class DeleteTicketAction
{
    public function execute(Ticket $ticket): void
    {
        $ticket->delete();
    }
}
