<?php

namespace App\Http\Controllers\Front\Support;

use App\Application\Support\Actions\CreateTicketAction;
use App\Application\Support\Exceptions\TicketLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Support\StoreTicketRequest;
use DomainException;
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

        try {
            $ticket = $this->createTicketAction->execute(
                user: $request->user(),
                departmentId: (int)$data['department_id'],
                productId: filled($data['product_id'] ?? null) ? (int)$data['product_id'] : null,
                title: $data['title'],
                message: $data['message'],
            );
        } catch (TicketLimitExceededException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'message' => $exception->getMessage(),
                ], 'createTicket');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'department_id' => $exception->getMessage(),
                ], 'createTicket');
        }

        return redirect()
            ->route('account.tickets.show', $ticket)
            ->with('ticket_created', true);
    }
}
