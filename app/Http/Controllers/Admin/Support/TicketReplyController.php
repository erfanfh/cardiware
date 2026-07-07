<?php

namespace App\Http\Controllers\Admin\Support;

use App\Application\Support\Actions\CreateTicketMessageAction;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\ReplyTicketRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class TicketReplyController extends Controller
{
    public function __construct(
        private readonly CreateTicketMessageAction $createTicketMessageAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ReplyTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('ticket.show');

        try {
            $this->createTicketMessageAction->execute(
                ticket: $ticket,
                sender: $request->user(),
                senderType: TicketMessageSenderTypeEnum::SUPPORT,
                body: $request->validated('message'),
            );
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'message' => $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'پاسخ پشتیبانی با موفقیت ثبت شد.');
    }
}
