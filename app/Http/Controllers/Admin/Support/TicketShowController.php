<?php

namespace App\Http\Controllers\Admin\Support;

use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketShowController extends Controller
{
    public function __invoke(Ticket $ticket): View
    {
        Gate::authorize('ticket.show');

        $ticket->load([
            'user',
            'department',
            'product',
            'closedByUser',
        ]);

        $messages = $ticket->messages()
            ->with('user')
            ->oldest('created_at')
            ->oldest('id')
            ->get();

        return view('admin.support.tickets.show', compact(
            'ticket',
            'messages',
        ));
    }
}
