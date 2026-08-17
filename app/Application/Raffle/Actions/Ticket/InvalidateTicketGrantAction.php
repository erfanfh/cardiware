<?php

namespace App\Application\Raffle\Actions\Ticket;

use App\Application\Raffle\DTOs\InvalidateTicketGrantDTO;
use App\Application\Raffle\Exceptions\DrawTicketInvalidationNotAllowedException;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Events\DrawTicketsInvalidatedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class InvalidateTicketGrantAction
{
    /** @throws Throwable */
    public function execute(InvalidateTicketGrantDTO $data): TicketGrant
    {
        $this->validate($data);

        return DB::transaction(function () use ($data): TicketGrant {
            $campaign = DrawCampaign::query()
                ->whereKey($data->grant->campaign_id)
                ->lockForUpdate()
                ->firstOrFail();

            $grant = TicketGrant::query()
                ->whereKey($data->grant->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($grant->isInvalidated()) {
                return $grant;
            }

            if (!$campaign->status->allowsTicketInvalidation()) {
                throw new DrawTicketInvalidationNotAllowedException(
                    'Ticket grants cannot be invalidated after the draw has started.'
                );
            }

            $occurredAt = $data->occurredAt ?? now()->toImmutable();

            $activeGrantTickets = DrawTicket::query()
                ->where('ticket_grant_id', $grant->getKey())
                ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                ->count();

            $activeUserTickets = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('user_id', $grant->user_id)
                ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                ->count();

            if ($activeGrantTickets > 0) {
                DrawTicket::query()
                    ->where('ticket_grant_id', $grant->getKey())
                    ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                    ->update([
                        'status' => DrawTicketStatusEnum::INVALIDATED->value,
                        'invalidated_at' => $occurredAt,
                        'invalidated_by_user_id' => $data->invalidatedBy?->getKey(),
                        'invalidation_reason' => $data->reason,
                        'updated_at' => now(),
                    ]);
            }

            $grant->forceFill([
                'invalidated_at' => $occurredAt,
                'invalidated_by_user_id' => $data->invalidatedBy?->getKey(),
                'invalidation_reason' => $data->reason,
            ])->save();

            $participantRemoved = $activeGrantTickets > 0
                && $activeUserTickets === $activeGrantTickets;

            $campaign->forceFill([
                'active_tickets_count' => max(
                    0,
                    (int)$campaign->active_tickets_count - $activeGrantTickets,
                ),
                'participants_count' => max(
                    0,
                    (int)$campaign->participants_count - ($participantRemoved ? 1 : 0),
                ),
            ])->save();

            if ($activeGrantTickets > 0) {
                DB::afterCommit(fn() => event(new DrawTicketsInvalidatedEvent(
                    campaignId: (int)$campaign->getKey(),
                    userId: (int)$grant->user_id,
                    ticketIds: [],
                    quantity: $activeGrantTickets,
                    reason: $data->reason,
                    actorUserId: $data->invalidatedBy?->getKey(),
                    occurredAt: $occurredAt,
                    ticketGrantId: (int)$grant->getKey(),
                )));
            }

            return $grant->refresh();
        }, 3);
    }

    private function validate(InvalidateTicketGrantDTO $data): void
    {
        if (!$data->grant->exists) {
            throw new InvalidArgumentException('Ticket grant must exist before it can be invalidated.');
        }

        if (trim($data->reason) === '' || Str::length($data->reason) > 255) {
            throw new InvalidArgumentException(
                'Ticket grant invalidation reason is required and cannot exceed 255 characters.'
            );
        }

        if ($data->invalidatedBy && !$data->invalidatedBy->exists) {
            throw new InvalidArgumentException('Ticket grant invalidation actor must exist.');
        }
    }
}
