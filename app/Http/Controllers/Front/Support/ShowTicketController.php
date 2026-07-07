<?php

namespace App\Http\Controllers\Front\Support;

use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowTicketController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket): View
    {
        $user = app(HydrateAccountUserAction::class)->execute(auth()->user());

        abort_unless($ticket->user_id === $user->id, 404);

        $ticket->load([
            'user',
            'department',
            'product',
        ]);

        return view('front.account.pages.tickets.show', [
            'user' => $user,
            'ticket' => $ticket,
        ]);
    }
}
