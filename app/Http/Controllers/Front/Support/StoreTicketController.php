<?php

namespace App\Http\Controllers\Front\Support;

use App\Application\Support\Actions\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Support\StoreTicketRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class StoreTicketController extends Controller
{
    public function __construct(
        private readonly CreateTicketAction $createTicketAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(StoreTicketRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->createTicketAction->handle(
            user: $request->user(),
            title: $data['title'],
            message: $data['message'],
        );

        return redirect()
            ->route('account.tickets')
            ->with('ticket_created', true);
    }
}
