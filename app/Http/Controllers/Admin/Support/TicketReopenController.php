<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\ReopenTicketAction;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TicketReopenController extends Controller
{
    public function __construct(
        private readonly ReopenTicketAction $reopenTicketAction,
    )
    {
    }

    public function __invoke(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('ticket.change-status');

        $this->reopenTicketAction->execute($ticket);

        return redirect()
            ->back()
            ->with('success', 'تیکت با موفقیت باز شد.');
    }
}
