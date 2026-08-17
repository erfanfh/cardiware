<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Application\Raffle\Exceptions\DrawStartNotAllowedException;
use App\Application\Raffle\Jobs\ExecuteDrawRunJob;
use App\Application\Raffle\Support\DrawAlgorithm;
use App\Application\Raffle\Support\DrawRunSeedManager;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawRunTypeEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawRedrawStartedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartRedrawRunAction
{
    public function __construct(private readonly DrawRunSeedManager $seedManager)
    {
    }

    /** @throws Throwable */
    public function execute(DrawWinner $failedWinner, ?User $actor = null): DrawRun
    {
        return DB::transaction(function () use ($failedWinner, $actor): DrawRun {
            $failed = DrawWinner::query()->whereKey($failedWinner->getKey())->lockForUpdate()->firstOrFail();
            $campaign = DrawCampaign::query()->whereKey($failed->campaign_id)->lockForUpdate()->firstOrFail();

            if ($campaign->status !== DrawCampaignStatusEnum::COMPLETED) {
                throw new DrawStartNotAllowedException('Redraw is only available after the original campaign draw is completed.');
            }
            if (!$failed->isEffectivePrimary()) {
                throw new DrawStartNotAllowedException('Redraw target must represent a primary prize position.');
            }
            if (!in_array($failed->status, [DrawWinnerStatusEnum::REJECTED, DrawWinnerStatusEnum::EXPIRED], true)) {
                throw new DrawStartNotAllowedException('Only a rejected or expired winner can trigger a redraw.');
            }
            if ($failed->hasReplacement()) {
                throw new DrawStartNotAllowedException('This winner has already been replaced.');
            }

            $availableReserveExists = DrawWinner::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('prize_id', $failed->prize_id)
                ->where('kind', DrawWinnerKindEnum::RESERVE)
                ->where('status', DrawWinnerStatusEnum::PENDING)
                ->whereNull('promoted_at')
                ->whereNull('replaces_winner_id')
                ->exists();

            if ($availableReserveExists) {
                throw new DrawStartNotAllowedException('A reserve winner is still available and must be promoted before starting a redraw.');
            }

            if (DrawRun::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('type', DrawRunTypeEnum::REDRAW)
                ->where('metadata->redraw_target->failed_winner_id', $failed->getKey())
                ->exists()) {
                throw new DrawStartNotAllowedException('A redraw run already exists for this failed winner.');
            }

            $prize = $failed->prize()->firstOrFail();
            $primaryPosition = $failed->effectivePrimaryPosition();
            $reserveCount = (int)$prize->effectiveReserveWinnersCount();

            $excludedTicketIds = DrawWinner::query()
                ->where('campaign_id', $campaign->getKey())
                ->pluck('ticket_id')
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values()
                ->all();

            $excludedUserIds = [(int)$failed->user_id];
            if (!$campaign->allow_multiple_wins_per_user) {
                $excludedUserIds = DrawWinner::query()
                    ->where('campaign_id', $campaign->getKey())
                    ->pluck('user_id')
                    ->map(fn($id) => (int)$id)
                    ->push((int)$failed->user_id)
                    ->unique()
                    ->values()
                    ->all();
            }

            $eligibleQuery = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('status', \App\Domain\Raffle\Enums\DrawTicketStatusEnum::ACTIVE)
                ->whereNotIn('id', $excludedTicketIds)
                ->whereNotIn('user_id', $excludedUserIds);

            $eligibleTickets = (clone $eligibleQuery)->count();
            $eligibleCandidates = $campaign->allow_multiple_wins_per_user
                ? $eligibleTickets
                : (clone $eligibleQuery)->distinct()->count('user_id');

            if ($eligibleCandidates < 1) {
                throw new DrawStartNotAllowedException('No eligible candidate remains for this redraw.');
            }

            $candidateLimit = min($eligibleCandidates, 1 + $reserveCount);
            $seed = $this->seedManager->generate();
            $commitment = $this->seedManager->commitment($seed);
            $now = now()->toImmutable();

            $run = DrawRun::query()->create([
                'campaign_id' => $campaign->getKey(),
                'type' => DrawRunTypeEnum::REDRAW,
                'status' => DrawRunStatusEnum::PENDING,
                'algorithm' => DrawAlgorithm::NAME,
                'seed_commitment' => $commitment,
                'seed_ciphertext' => $this->seedManager->encrypt($seed),
                'started_by_user_id' => $actor?->getKey(),
                'metadata' => [
                    'prize_plan' => [[
                        'prize_id' => (int)$prize->getKey(),
                        'title' => (string)$prize->title,
                        'quantity' => 1,
                        'reserve_count' => $reserveCount,
                        'sort_order' => (int)$prize->sort_order,
                    ]],
                    'primary_slots' => 1,
                    'reserve_slots_requested' => $reserveCount,
                    'candidate_limit' => $candidateLimit,
                    'eligible_candidates_preflight' => $eligibleCandidates,
                    'eligible_tickets_preflight' => $eligibleTickets,
                    'allow_multiple_wins_per_user' => (bool)$campaign->allow_multiple_wins_per_user,
                    'excluded_ticket_ids' => $excludedTicketIds,
                    'excluded_user_ids' => $excludedUserIds,
                    'redraw_target' => [
                        'failed_winner_id' => (int)$failed->getKey(),
                        'prize_id' => (int)$failed->prize_id,
                        'primary_position' => $primaryPosition,
                    ],
                    'created_at' => $now->toIso8601String(),
                    'attempts' => [],
                ],
            ]);

            DB::afterCommit(function () use ($campaign, $run, $failed, $primaryPosition, $actor, $commitment, $now): void {
                event(new DrawRedrawStartedEvent(
                    campaignId: (int)$campaign->getKey(),
                    drawRunId: (int)$run->getKey(),
                    failedWinnerId: (int)$failed->getKey(),
                    prizeId: (int)$failed->prize_id,
                    primaryPosition: (int)$primaryPosition,
                    actorUserId: $actor?->getKey(),
                    seedCommitment: $commitment,
                    occurredAt: $now,
                ));

                ExecuteDrawRunJob::dispatch((int)$run->getKey());
            });

            return $run->refresh();
        }, 3);
    }
}
