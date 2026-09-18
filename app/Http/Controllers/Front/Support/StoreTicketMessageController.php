<?php

namespace App\Http\Controllers\Front\Support;

use App\Application\Support\Actions\CreateTicketMessageAction;
use App\Application\Support\Exceptions\TicketAttachmentUploadException;
use App\Application\Support\Exceptions\TicketLimitExceededException;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Support\StoreTicketMessageRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Throwable;

class StoreTicketMessageController extends Controller
{
    public function __construct(
        private readonly CreateTicketMessageAction $createTicketMessageAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(StoreTicketMessageRequest $request, Ticket $ticket): RedirectResponse
    {
        $user = $request->user();

        abort_unless($ticket->user_id === $user->id, 404);

        try {
            $this->createTicketMessageAction->execute(
                ticket: $ticket,
                sender: $user,
                senderType: TicketMessageSenderTypeEnum::USER,
                body: $request->validated('message'),
                attachments: $request->file('attachments', []),
            );
        } catch (TicketLimitExceededException|DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'message' => $exception->getMessage(),
                ], 'replyTicket');
        } catch (TicketAttachmentUploadException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'attachments' => $exception->getMessage(),
                ], 'replyTicket');
        }

        return redirect()
            ->route('account.tickets.show', $ticket)
            ->with('ticket_message_created', true);
    }
}
