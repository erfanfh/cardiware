<?php

namespace App\Application\Support\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTicketAction
{
    public function __construct(
        private readonly CreateTicketMessageAction $createTicketMessageAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(User $user, string $title, string $message): Ticket
    {
        return DB::transaction(function () use ($user, $title, $message) {
            $ticket = Ticket::query()->create([
                'user_id' => $user->id,
                'title' => $title,
                'status' => TicketStatusEnum::OPEN,
                'last_message_at' => null,
                'last_user_message_at' => null,
                'last_support_message_at' => null,
            ]);

            $this->createTicketMessageAction->handle(
                ticket: $ticket,
                sender: $user,
                senderType: TicketMessageSenderTypeEnum::USER,
                body: $message,
            );

            return $ticket->refresh()->load([
                'user',
                'latestMessage',
            ]);
        });
    }
}
