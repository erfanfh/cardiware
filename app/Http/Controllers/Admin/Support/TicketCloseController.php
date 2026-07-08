<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\CloseTicketAction;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketCloseController extends Controller
{
    public function __construct(
        private readonly CloseTicketAction $closeTicketAction,
    )
    {
    }

    public function __invoke(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('ticket.change-status');

        $this->closeTicketAction->execute($ticket, $request->user());

        return redirect()
            ->back()
            ->with('success', 'تیکت با موفقیت بسته شد.');
    }
}
