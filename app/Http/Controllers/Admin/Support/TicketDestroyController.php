<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\DeleteTicketAction;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TicketDestroyController extends Controller
{
    public function __invoke(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('ticket.delete');

        app(DeleteTicketAction::class)->execute($ticket);

        return redirect()
            ->route('admin.tickets.index')
            ->with('success', 'تیکت با موفقیت حذف شد.');
    }
}
