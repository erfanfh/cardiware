<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Events\DrawCampaignUpdatedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateDrawCampaignAction
{
    public function __construct(
        private readonly SyncDrawPrizesAction $syncPrizes,
        private readonly AssignSlugAction $assignSlug,
    ) {}

    /** @param array<string,mixed> $data @throws Throwable */
    public function execute(DrawCampaign $campaign, array $data, ?User $actor = null): DrawCampaign
    {
        return DB::transaction(function () use ($campaign, $data, $actor): DrawCampaign {
            $campaign = DrawCampaign::query()->whereKey($campaign->getKey())->lockForUpdate()->firstOrFail();
            $before = $campaign->only([
                'title', 'slug', 'short_description', 'body', 'rules', 'cover_media_file_id',
                'starts_at', 'closes_at', 'draw_at', 'ticket_limit', 'ticket_limit_per_user',
                'allow_multiple_wins_per_user', 'reserve_winners_per_prize',
                'winner_confirmation_window_hours', 'show_winners_publicly',
                'points_per_ticket', 'ticket_price',
            ]);

            $structureEditable = in_array($campaign->status, [
                DrawCampaignStatusEnum::DRAFT,
                DrawCampaignStatusEnum::SCHEDULED,
            ], true) && (int) $campaign->issued_tickets_count === 0;

            $common = [
                'title' => $data['title'],
                'short_description' => $data['short_description'] ?? null,
                'body' => $data['body'] ?? null,
                'rules' => $data['rules'] ?? null,
                'cover_media_file_id' => $data['cover_media_file_id'] ?? null,
                'show_winners_publicly' => (bool) ($data['show_winners_publicly'] ?? false),
            ] + array_intersect_key($data, array_flip(['points_per_ticket', 'ticket_price']));

            if ($structureEditable) {
                $common += [
                    'starts_at' => $data['starts_at'] ?? null,
                    'closes_at' => $data['closes_at'] ?? null,
                    'draw_at' => $data['draw_at'] ?? null,
                    'ticket_limit' => $data['ticket_limit'] ?? null,
                    'ticket_limit_per_user' => $data['ticket_limit_per_user'] ?? null,
                    'allow_multiple_wins_per_user' => (bool) ($data['allow_multiple_wins_per_user'] ?? false),
                    'reserve_winners_per_prize' => (int) ($data['reserve_winners_per_prize'] ?? 3),
                    'winner_confirmation_window_hours' => $data['winner_confirmation_window_hours'] ?? 72,
                ];
            }

            $campaign->update($common);

            $this->assignSlug->execute(new AssignSlugDTO(
                scope: SlugScopeEnum::RAFFLE,
                model: $campaign,
                desiredSlug: first_filled($data['slug'] ?? null, $data['title'] ?? null),
            ));

            if ($structureEditable) {
                $this->syncPrizes->execute($campaign, $data['prizes'] ?? []);
            }

            $campaign->refresh();
            $after = $campaign->only(array_keys($before));
            $changedFields = collect($after)
                ->filter(fn ($value, $key) => $value != $before[$key])
                ->keys()
                ->values()
                ->all();

            if ($changedFields !== []) {
                DB::afterCommit(fn () => event(new DrawCampaignUpdatedEvent(
                    campaignId: (int) $campaign->getKey(),
                    actorUserId: $actor?->getKey(),
                    changedFields: $changedFields,
                    occurredAt: now()->toImmutable(),
                )));
            }

            return $campaign->load(['prizes.mediaFile', 'coverMediaFile']);
        });
    }
}
