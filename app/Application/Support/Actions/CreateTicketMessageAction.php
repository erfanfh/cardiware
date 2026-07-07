<?php

namespace App\Application\Support\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketMessage;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTicketMessageAction
{
    /**
     * @throws Throwable
     */
    public function handle(
        Ticket                      $ticket,
        ?User                       $sender,
        TicketMessageSenderTypeEnum $senderType,
        string                      $body,
        bool                        $isInternalNote = false,
    ): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $sender, $senderType, $body, $isInternalNote) {
            $ticket->refresh();

            if (!$ticket->canReceiveReply()) {
                throw new DomainException('امکان ارسال پیام برای تیکت بسته‌شده وجود ندارد.');
            }

            if ($senderType !== TicketMessageSenderTypeEnum::SYSTEM && !$sender) {
                throw new DomainException('فرستنده پیام تیکت مشخص نیست.');
            }

            if ($senderType === TicketMessageSenderTypeEnum::USER) {
                $this->ensureUserOwnsTicket($ticket, $sender);
                $isInternalNote = false;
            }

            if ($senderType === TicketMessageSenderTypeEnum::SYSTEM) {
                $isInternalNote = false;
            }

            $message = $ticket->messages()->create([
                'user_id' => $sender?->id,
                'sender_type' => $senderType,
                'body' => trim($body),
                'is_internal_note' => $isInternalNote,
            ]);

            $this->updateTicketActivity(
                ticket: $ticket,
                senderType: $senderType,
                isInternalNote: $isInternalNote,
            );

            return $message->load([
                'ticket',
                'user',
            ]);
        });
    }

    private function ensureUserOwnsTicket(Ticket $ticket, ?User $sender): void
    {
        if (!$sender || $ticket->user_id !== $sender->id) {
            throw new DomainException('شما اجازه ارسال پیام برای این تیکت را ندارید.');
        }
    }

    private function updateTicketActivity(
        Ticket                      $ticket,
        TicketMessageSenderTypeEnum $senderType,
        bool                        $isInternalNote,
    ): void
    {
        if ($isInternalNote) {
            return;
        }

        $now = now();

        $updates = [
            'last_message_at' => $now,
        ];

        if ($senderType === TicketMessageSenderTypeEnum::USER) {
            $updates['status'] = TicketStatusEnum::OPEN;
            $updates['last_user_message_at'] = $now;
        }

        if ($senderType === TicketMessageSenderTypeEnum::SUPPORT) {
            $updates['status'] = TicketStatusEnum::ANSWERED;
            $updates['last_support_message_at'] = $now;
        }

        $ticket->forceFill($updates)->save();
    }
}
