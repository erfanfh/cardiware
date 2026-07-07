<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\CloseTicketAction;
use App\Application\Support\Actions\ReopenTicketAction;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\TicketBulkActionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TicketBulkActionController extends Controller
{
    public function __construct(
        private readonly CloseTicketAction  $closeTicketAction,
        private readonly ReopenTicketAction $reopenTicketAction,
    )
    {
    }

    public function __invoke(TicketBulkActionRequest $request): RedirectResponse
    {
        Gate::authorize('ticket.change-status');

        $data = $request->validated();

        $tickets = Ticket::query()
            ->whereIn('id', $data['ids'])
            ->get();

        foreach ($tickets as $ticket) {
            if ($data['bulk_action'] === 'close') {
                $this->closeTicketAction->execute($ticket, $request->user());
            }

            if ($data['bulk_action'] === 'reopen') {
                $this->reopenTicketAction->execute($ticket);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'عملیات گروهی تیکت‌ها با موفقیت انجام شد.');
    }
}
