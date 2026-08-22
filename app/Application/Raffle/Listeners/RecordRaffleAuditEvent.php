<?php

namespace App\Application\Raffle\Listeners;

use App\Domain\Raffle\Models\DrawCampaignEvent;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

class RecordRaffleAuditEvent
{
    public function handle(object $event): void
    {
        $data = get_object_vars($event);
        $campaignId = (int) ($data['campaignId'] ?? 0);
        if ($campaignId <= 0) {
            return;
        }

        [$subjectType, $subjectId] = $this->subject($data);

        try {
            DrawCampaignEvent::query()->create([
                'campaign_id' => $campaignId,
                'actor_user_id' => isset($data['actorUserId']) ? (int) $data['actorUserId'] : null,
                'type' => $this->eventType($event),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'payload' => $this->normalize($data),
                'occurred_at' => $data['occurredAt'] ?? now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function eventType(object $event): string
    {
        $name = class_basename($event);
        $name = preg_replace('/Event$/', '', $name) ?: $name;

        return match ($name) {
            'DrawCampaignCreated' => 'campaign_created',
            'DrawCampaignUpdated' => 'campaign_updated',
            'DrawTicketsIssued' => 'tickets_issued',
            'DrawTicketsInvalidated' => 'tickets_invalidated',
            'DrawStarted' => 'draw_started',
            'DrawRedrawStarted' => 'draw_redraw_started',
            'DrawCompleted' => 'draw_completed',
            'DrawFailed' => 'draw_failed',
            'DrawWinnersSelected' => 'winners_selected',
            'DrawWinnerConfirmed' => 'winner_confirmed',
            'DrawWinnerRejected' => 'winner_rejected',
            'DrawWinnerExpired' => 'winner_expired',
            'DrawReservePromoted' => 'reserve_promoted',
            'DrawPrizeDelivered' => 'prize_delivered',
            'DrawCampaignStatusChanged' => 'campaign_status_changed',
            'DrawCampaignCancelled' => 'campaign_cancelled',
            default => Str::snake($name),
        };
    }

    private function subject(array $data): array
    {
        foreach (['winnerId' => 'winner', 'drawRunId' => 'draw_run', 'ticketGrantId' => 'ticket_grant'] as $key => $type) {
            if (isset($data[$key])) {
                return [$type, (int) $data[$key]];
            }
        }

        return [null, null];
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->toIso8601String();
        }
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalize($item), $value);
        }

        return $value;
    }
}
