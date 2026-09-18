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
    public function __construct(
        private readonly EnsureCanReplyTicketAction $ensureCanReplyTicketAction,
        private readonly StoreTicketMessageAttachmentsAction $storeAttachmentsAction,
        private readonly DeleteTicketMessageAttachmentAction $deleteAttachmentAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(
        Ticket $ticket,
        ?User $sender,
        TicketMessageSenderTypeEnum $senderType,
        string $body,
        bool $enforceUserReplyLimits = true,
        array $attachments = [],
    ): TicketMessage {
        $storedAttachments = [];

        try {
            return DB::transaction(function () use (
                $ticket,
                $sender,
                $senderType,
                $body,
                $enforceUserReplyLimits,
                $attachments,
                &$storedAttachments,
            ) {
                $ticket->refresh();

                if (! $ticket->canReceiveReply()) {
                    throw new DomainException('امکان ارسال پیام برای تیکت بسته‌شده وجود ندارد.');
                }

                if ($senderType !== TicketMessageSenderTypeEnum::SYSTEM && ! $sender) {
                    throw new DomainException('فرستنده پیام تیکت مشخص نیست.');
                }

                if ($senderType === TicketMessageSenderTypeEnum::USER) {
                    $this->ensureUserOwnsTicket($ticket, $sender);

                    if ($enforceUserReplyLimits) {
                        $this->ensureCanReplyTicketAction->execute(
                            ticket: $ticket,
                            user: $sender,
                            body: $body,
                        );
                    }
                }

                $message = $ticket->messages()->create([
                    'user_id' => $sender?->id,
                    'sender_type' => $senderType,
                    'body' => trim($body),
                ]);

                $this->updateTicketActivity(
                    ticket: $ticket,
                    senderType: $senderType,
                );

                if ($attachments !== []) {
                    if (! $sender) {
                        throw new DomainException('فرستنده تصاویر تیکت مشخص نیست.');
                    }

                    $storedAttachments = $this->storeAttachmentsAction->execute(
                        message: $message,
                        files: $attachments,
                        uploader: $sender,
                    );
                }

                return $message->load([
                    'ticket',
                    'user',
                    'attachments.variants',
                ]);
            });
        } catch (Throwable $exception) {
            foreach ($storedAttachments as $attachment) {
                try {
                    $this->deleteAttachmentAction->execute($attachment);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }
    }

    private function ensureUserOwnsTicket(Ticket $ticket, ?User $sender): void
    {
        if (! $sender || $ticket->user_id !== $sender->id) {
            throw new DomainException('شما اجازه ارسال پیام برای این تیکت را ندارید.');
        }
    }

    private function updateTicketActivity(
        Ticket $ticket,
        TicketMessageSenderTypeEnum $senderType,
    ): void {
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
