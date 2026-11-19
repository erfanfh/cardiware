<?php

namespace App\Application\Raffle\Actions\Ticket;

use App\Application\Raffle\Actions\CheckRaffleEligibilityAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Application\Raffle\Exceptions\DrawCampaignNotAcceptingTicketsException;
use App\Application\Raffle\Exceptions\DrawParticipantIneligibleException;
use App\Application\Raffle\Exceptions\DrawTicketCapacityExceededException;
use App\Application\Raffle\Exceptions\DrawUserTicketLimitExceededException;
use App\Application\Raffle\Support\DrawTicketCodeGenerator;
use App\Application\Raffle\Support\TicketGrantFingerprint;
use App\Application\Raffle\Support\TicketGrantIdempotencyGuard;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Events\DrawTicketsIssuedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class IssueDrawTicketsAction
{
    public function __construct(
        private readonly TicketGrantFingerprint $fingerprint,
        private readonly TicketGrantIdempotencyGuard $idempotencyGuard,
        private readonly DrawTicketCodeGenerator $ticketCodeGenerator,
        private readonly CheckRaffleEligibilityAction $checkEligibility,
    ) {
    }

    /** @throws Throwable */
    public function execute(IssueDrawTicketsDTO $data): TicketGrant
    {
        $this->validate($data);
        $fingerprint = $this->fingerprint->forIssue($data);

        if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data, $fingerprint): TicketGrant {
                $campaign = DrawCampaign::query()
                    ->whereKey($data->campaign->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                    return $existing;
                }

                $occurredAt = $data->occurredAt ?? now()->toImmutable();

                if (!$campaign->acceptsTicketsAt($occurredAt)) {
                    throw new DrawCampaignNotAcceptingTicketsException(
                        'This draw campaign is not currently accepting new tickets.'
                    );
                }

                $isVipGift = ($data->metadata['origin'] ?? null) === 'vip_monthly_gift';
                if (
                    ($isVipGift && (!$campaign->vip_monthly_ticket || !$data->user->hasActiveVipSubscription()))
                    || !$this->checkEligibility->execute($campaign, $data->user)['eligible']
                ) {
                    throw new DrawParticipantIneligibleException('شرایط دریافت بلیط این قرعه‌کشی را ندارید.');
                }

                if (
                    $campaign->ticket_limit !== null
                    && ((int)$campaign->active_tickets_count + $data->quantity) > (int)$campaign->ticket_limit
                ) {
                    throw new DrawTicketCapacityExceededException(
                        'The draw campaign does not have enough remaining ticket capacity.'
                    );
                }

                $activeUserTickets = DrawTicket::query()
                    ->where('campaign_id', $campaign->getKey())
                    ->where('user_id', $data->user->getKey())
                    ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                    ->count();

                if (
                    $campaign->ticket_limit_per_user !== null
                    && ($activeUserTickets + $data->quantity) > (int)$campaign->ticket_limit_per_user
                ) {
                    throw new DrawUserTicketLimitExceededException(
                        'The requested tickets exceed the per-user ticket limit for this campaign.'
                    );
                }

                $grant = TicketGrant::query()->create([
                    'campaign_id' => $campaign->getKey(),
                    'user_id' => $data->user->getKey(),
                    'quantity' => $data->quantity,
                    'source_type' => $data->source?->getMorphClass(),
                    'source_id' => $data->source?->getKey(),
                    'idempotency_key' => $data->idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
                    'issued_by_user_id' => $data->issuedBy?->getKey(),
                    'issued_at' => $occurredAt,
                    'metadata' => $data->metadata,
                ]);

                $this->insertTickets(
                    campaign: $campaign,
                    grant: $grant,
                    userId: (int)$data->user->getKey(),
                    quantity: $data->quantity,
                    issuedAt: $occurredAt,
                );

                $campaign->forceFill([
                    'issued_tickets_count' => (int)$campaign->issued_tickets_count + $data->quantity,
                    'active_tickets_count' => (int)$campaign->active_tickets_count + $data->quantity,
                    'participants_count' => (int)$campaign->participants_count + ($activeUserTickets === 0 ? 1 : 0),
                ])->save();

                DB::afterCommit(fn() => event(new DrawTicketsIssuedEvent(
                    campaignId: (int)$campaign->getKey(),
                    ticketGrantId: (int)$grant->getKey(),
                    userId: (int)$data->user->getKey(),
                    quantity: $data->quantity,
                    occurredAt: $occurredAt,
                )));

                return $grant->refresh();
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function validate(IssueDrawTicketsDTO $data): void
    {
        if (!$data->campaign->exists) {
            throw new InvalidArgumentException('Draw campaign must exist before tickets can be issued.');
        }

        if (!$data->user->exists) {
            throw new InvalidArgumentException('Ticket owner must exist.');
        }

        if ($data->quantity <= 0) {
            throw new InvalidArgumentException('Ticket quantity must be greater than zero.');
        }

        if (
            trim($data->idempotencyKey) === ''
            || Str::length($data->idempotencyKey) > 160
        ) {
            throw new InvalidArgumentException(
                'Ticket grant idempotency key is required and cannot exceed 160 characters.'
            );
        }

        if ($data->source && !$data->source->exists) {
            throw new InvalidArgumentException('Ticket grant source must exist.');
        }

        if ($data->issuedBy && !$data->issuedBy->exists) {
            throw new InvalidArgumentException('Ticket issuer must exist.');
        }
    }

    private function insertTickets(
        DrawCampaign $campaign,
        TicketGrant $grant,
        int $userId,
        int $quantity,
        mixed $issuedAt,
    ): void {
        $remaining = $quantity;

        while ($remaining > 0) {
            $chunkSize = min(500, $remaining);
            $codes = $this->ticketCodeGenerator->generateBatch($campaign, $chunkSize);
            $now = now();
            $rows = [];

            foreach ($codes as $code) {
                $rows[] = [
                    'public_token' => (string)Str::ulid(),
                    'campaign_id' => $campaign->getKey(),
                    'ticket_grant_id' => $grant->getKey(),
                    'user_id' => $userId,
                    'code' => $code,
                    'status' => DrawTicketStatusEnum::ACTIVE->value,
                    'issued_at' => $issuedAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('draw_tickets')->insert($rows);
            $remaining -= $chunkSize;
        }
    }
}
