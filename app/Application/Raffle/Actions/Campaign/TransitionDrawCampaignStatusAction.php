<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Application\Raffle\DTOs\TransitionDrawCampaignStatusDTO;
use App\Application\Raffle\Support\DrawCampaignStatusTransitionGuard;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Events\DrawCampaignCancelledEvent;
use App\Domain\Raffle\Events\DrawCampaignStatusChangedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class TransitionDrawCampaignStatusAction
{
    public function __construct(
        private readonly DrawCampaignStatusTransitionGuard $transitionGuard,
    ) {
    }

    /** @throws Throwable */
    public function execute(TransitionDrawCampaignStatusDTO $data): DrawCampaign
    {
        $this->validate($data);

        if (in_array($data->status, [
            DrawCampaignStatusEnum::DRAWING,
            DrawCampaignStatusEnum::COMPLETED,
        ], true)) {
            throw new InvalidArgumentException(
                'DRAWING and COMPLETED statuses can only be managed by the raffle draw engine.'
            );
        }

        return DB::transaction(function () use ($data): DrawCampaign {
            $campaign = DrawCampaign::query()
                ->whereKey($data->campaign->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $from = $campaign->status;

            if ($from === $data->status) {
                return $campaign;
            }

            $occurredAt = $data->occurredAt ?? now()->toImmutable();

            $this->transitionGuard->ensureAllowed(
                campaign: $campaign,
                targetStatus: $data->status,
                occurredAt: $occurredAt,
            );

            $attributes = [
                'status' => $data->status,
            ];

            match ($data->status) {
                DrawCampaignStatusEnum::OPEN => $attributes['opened_at'] = $occurredAt,
                DrawCampaignStatusEnum::CLOSED => $attributes['closed_at'] = $occurredAt,
                DrawCampaignStatusEnum::DRAWING => $attributes['drawing_started_at'] = $occurredAt,
                DrawCampaignStatusEnum::COMPLETED => $attributes['completed_at'] = $occurredAt,
                DrawCampaignStatusEnum::CANCELLED => $attributes['cancelled_at'] = $occurredAt,
                default => null,
            };

            if ($data->status === DrawCampaignStatusEnum::CANCELLED) {
                $invalidationReason = Str::limit(
                    trim((string)($data->reason ?: 'campaign_cancelled')),
                    255,
                    '',
                );

                DB::table('draw_tickets')
                    ->where('campaign_id', $campaign->getKey())
                    ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                    ->update([
                        'status' => DrawTicketStatusEnum::INVALIDATED->value,
                        'invalidated_at' => $occurredAt,
                        'invalidated_by_user_id' => $data->actor?->getKey(),
                        'invalidation_reason' => $invalidationReason,
                        'updated_at' => now(),
                    ]);

                DB::table('draw_ticket_grants')
                    ->where('campaign_id', $campaign->getKey())
                    ->whereNull('invalidated_at')
                    ->update([
                        'invalidated_at' => $occurredAt,
                        'invalidated_by_user_id' => $data->actor?->getKey(),
                        'invalidation_reason' => $invalidationReason,
                    ]);

                $attributes['active_tickets_count'] = 0;
                $attributes['participants_count'] = 0;
            }

            $campaign->forceFill($attributes)->save();

            DB::afterCommit(fn() => event(new DrawCampaignStatusChangedEvent(
                campaignId: (int)$campaign->getKey(),
                from: $from,
                to: $data->status,
                actorUserId: $data->actor?->getKey(),
                occurredAt: $occurredAt,
                reason: $data->reason,
            )));

            if ($data->status === DrawCampaignStatusEnum::CANCELLED) {
                DB::afterCommit(fn() => event(new DrawCampaignCancelledEvent(
                    campaignId: (int)$campaign->getKey(),
                    actorUserId: $data->actor?->getKey(),
                    occurredAt: $occurredAt,
                    reason: $data->reason,
                )));
            }

            return $campaign->refresh();
        }, 3);
    }

    private function validate(TransitionDrawCampaignStatusDTO $data): void
    {
        if (!$data->campaign->exists) {
            throw new InvalidArgumentException('Draw campaign must exist before its status can be changed.');
        }

        if ($data->actor && !$data->actor->exists) {
            throw new InvalidArgumentException('Draw campaign transition actor must exist.');
        }

        if ($data->reason !== null && Str::length($data->reason) > 1000) {
            throw new InvalidArgumentException('Draw campaign transition reason cannot exceed 1000 characters.');
        }
    }
}
