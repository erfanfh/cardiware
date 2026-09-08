<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\GetTicketFormProductsAction;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;
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
            ->with([
                'user',
                'attachments.variants',
            ])
            ->oldest('created_at')
            ->oldest('id')
            ->get();

        $departments = TicketDepartment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'requires_product',
            ]);

        $ticketProducts = app(GetTicketFormProductsAction::class)->execute($ticket->user);

        return view('admin.support.tickets.show', compact(
            'ticket',
            'messages',
            'departments',
            'ticketProducts',
        ));
    }
}
