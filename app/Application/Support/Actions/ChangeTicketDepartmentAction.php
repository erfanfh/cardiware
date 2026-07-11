<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;

class ChangeTicketDepartmentAction
{
    public function execute(
        Ticket           $ticket,
        TicketDepartment $department,
        ?int             $productId,
    ): void
    {
        $ticket->department()->associate($department);

        if ($department->requires_product) {
            $ticket->product()->associate($productId);
        } else {
            $ticket->product()->dissociate();
        }

        $ticket->save();
    }
}
