<?php

namespace App\Application\Raffle\Actions\Ticket;

use App\Application\Raffle\DTOs\InvalidateDrawTicketDTO;
use App\Application\Raffle\Exceptions\DrawTicketInvalidationNotAllowedException;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Events\DrawTicketsInvalidatedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class InvalidateDrawTicketAction
{
    /** @throws Throwable */
    public function execute(InvalidateDrawTicketDTO $data): DrawTicket
    {
        $this->validate($data);

        return DB::transaction(function () use ($data): DrawTicket {
            $campaign = DrawCampaign::query()
                ->whereKey($data->ticket->campaign_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ticket = DrawTicket::query()
                ->whereKey($data->ticket->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($ticket->status === DrawTicketStatusEnum::INVALIDATED) {
                return $ticket;
            }

            if (!$campaign->status->allowsTicketInvalidation()) {
                throw new DrawTicketInvalidationNotAllowedException(
                    'Tickets cannot be invalidated after the draw has started.'
                );
            }

            $occurredAt = $data->occurredAt ?? now()->toImmutable();

            $activeUserTickets = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('user_id', $ticket->user_id)
                ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                ->count();

            $ticket->forceFill([
                'status' => DrawTicketStatusEnum::INVALIDATED,
                'invalidated_at' => $occurredAt,
                'invalidated_by_user_id' => $data->invalidatedBy?->getKey(),
                'invalidation_reason' => $data->reason,
            ])->save();

            $campaign->forceFill([
                'active_tickets_count' => max(0, (int)$campaign->active_tickets_count - 1),
                'participants_count' => max(
                    0,
                    (int)$campaign->participants_count - ($activeUserTickets === 1 ? 1 : 0),
                ),
            ])->save();

            DB::afterCommit(fn() => event(new DrawTicketsInvalidatedEvent(
                campaignId: (int)$campaign->getKey(),
                userId: (int)$ticket->user_id,
                ticketIds: [(int)$ticket->getKey()],
                quantity: 1,
                reason: $data->reason,
                actorUserId: $data->invalidatedBy?->getKey(),
                occurredAt: $occurredAt,
                ticketGrantId: (int)$ticket->ticket_grant_id,
            )));

            return $ticket->refresh();
        }, 3);
    }

    private function validate(InvalidateDrawTicketDTO $data): void
    {
        if (!$data->ticket->exists) {
            throw new InvalidArgumentException('Draw ticket must exist before it can be invalidated.');
        }

        if (trim($data->reason) === '' || Str::length($data->reason) > 255) {
            throw new InvalidArgumentException(
                'Ticket invalidation reason is required and cannot exceed 255 characters.'
            );
        }

        if ($data->invalidatedBy && !$data->invalidatedBy->exists) {
            throw new InvalidArgumentException('Ticket invalidation actor must exist.');
        }
    }
}
