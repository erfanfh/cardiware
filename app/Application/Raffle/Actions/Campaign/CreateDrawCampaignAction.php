<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateDrawCampaignAction
{
    public function __construct(
        private readonly SyncDrawPrizesAction $syncPrizes,
        private readonly AssignSlugAction $assignSlug,
    ) {
    }

    /** @param array<string,mixed> $data @throws Throwable */
    public function execute(array $data, ?User $actor = null): DrawCampaign
    {
        return DB::transaction(function () use ($data, $actor): DrawCampaign {
            $campaign = DrawCampaign::query()->create([
                'title' => $data['title'],
                'slug' => 'pending-' . Str::lower((string) Str::ulid()),
                'short_description' => $data['short_description'] ?? null,
                'body' => $data['body'] ?? null,
                'rules' => $data['rules'] ?? null,
                'cover_media_file_id' => $data['cover_media_file_id'] ?? null,
                'status' => DrawCampaignStatusEnum::DRAFT,
                'starts_at' => $data['starts_at'] ?? null,
                'closes_at' => $data['closes_at'] ?? null,
                'draw_at' => $data['draw_at'] ?? null,
                'ticket_limit' => $data['ticket_limit'] ?? null,
                'ticket_limit_per_user' => $data['ticket_limit_per_user'] ?? null,
                'allow_multiple_wins_per_user' => (bool)($data['allow_multiple_wins_per_user'] ?? false),
                'reserve_winners_per_prize' => (int)($data['reserve_winners_per_prize'] ?? 3),
                'winner_confirmation_window_hours' => $data['winner_confirmation_window_hours'] ?? 72,
                'show_winners_publicly' => (bool)($data['show_winners_publicly'] ?? false),
                'created_by_user_id' => $actor?->getKey(),
            ]);

            $this->assignSlug->execute(new AssignSlugDTO(
                scope: SlugScopeEnum::RAFFLE,
                model: $campaign,
                desiredSlug: first_filled($data['slug'] ?? null, $data['title'] ?? null),
            ));

            $this->syncPrizes->execute($campaign, $data['prizes'] ?? []);

            return $campaign->load(['prizes.mediaFile', 'coverMediaFile']);
        });
    }
}
