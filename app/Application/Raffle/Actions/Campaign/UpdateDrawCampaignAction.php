<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateDrawCampaignAction
{
    public function __construct(
        private readonly SyncDrawPrizesAction $syncPrizes,
    ) {
    }

    /** @param array<string,mixed> $data @throws Throwable */
    public function execute(DrawCampaign $campaign, array $data): DrawCampaign
    {
        return DB::transaction(function () use ($campaign, $data): DrawCampaign {
            $campaign = DrawCampaign::query()->whereKey($campaign->getKey())->lockForUpdate()->firstOrFail();

            $structureEditable = in_array($campaign->status, [
                DrawCampaignStatusEnum::DRAFT,
                DrawCampaignStatusEnum::SCHEDULED,
            ], true) && (int)$campaign->issued_tickets_count === 0;

            $common = [
                'title' => $data['title'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'] ?? null,
                'body' => $data['body'] ?? null,
                'rules' => $data['rules'] ?? null,
                'cover_media_file_id' => $data['cover_media_file_id'] ?? null,
                'show_winners_publicly' => (bool)($data['show_winners_publicly'] ?? false),
            ];

            if ($structureEditable) {
                $common += [
                    'starts_at' => $data['starts_at'] ?? null,
                    'closes_at' => $data['closes_at'] ?? null,
                    'draw_at' => $data['draw_at'] ?? null,
                    'ticket_limit' => $data['ticket_limit'] ?? null,
                    'ticket_limit_per_user' => $data['ticket_limit_per_user'] ?? null,
                    'allow_multiple_wins_per_user' => (bool)($data['allow_multiple_wins_per_user'] ?? false),
                    'reserve_winners_per_prize' => (int)($data['reserve_winners_per_prize'] ?? 3),
                    'winner_confirmation_window_hours' => $data['winner_confirmation_window_hours'] ?? 72,
                ];
            }

            $campaign->update($common);

            if ($structureEditable) {
                $this->syncPrizes->execute($campaign, $data['prizes'] ?? []);
            }

            return $campaign->refresh()->load(['prizes.mediaFile', 'coverMediaFile']);
        });
    }
}
