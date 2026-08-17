<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Application\Raffle\Exceptions\DrawRunExecutionException;
use App\Application\Raffle\Support\DrawAlgorithm;
use App\Application\Raffle\Support\DrawCandidateSelector;
use App\Application\Raffle\Support\DrawRunSeedManager;
use App\Application\Raffle\Support\DrawWinnerPlanner;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawCampaignStatusChangedEvent;
use App\Domain\Raffle\Events\DrawCompletedEvent;
use App\Domain\Raffle\Events\DrawWinnersSelectedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ExecuteDrawRunAction
{
    public function __construct(
        private readonly DrawRunSeedManager $seedManager,
        private readonly DrawCandidateSelector $candidateSelector,
        private readonly DrawWinnerPlanner $winnerPlanner,
    ) {
    }

    /** @throws Throwable */
    public function execute(int $drawRunId): DrawRun
    {
        $lock = Cache::lock("raffle:draw-run:{$drawRunId}", 3700);

        if (!$lock->get()) {
            return DrawRun::query()->findOrFail($drawRunId);
        }

        try {
            return $this->executeLocked($drawRunId);
        } finally {
            optional($lock)->release();
        }
    }

    /** @throws Throwable */
    private function executeLocked(int $drawRunId): DrawRun
    {
        $run = DB::transaction(function () use ($drawRunId): DrawRun {
            $run = DrawRun::query()->whereKey($drawRunId)->lockForUpdate()->firstOrFail();

            if ($run->status === DrawRunStatusEnum::COMPLETED) {
                return $run;
            }

            if ($run->winners()->exists()) {
                throw new DrawRunExecutionException(
                    'Non-completed draw run already contains winner records and cannot be executed safely.'
                );
            }

            $campaign = DrawCampaign::query()
                ->whereKey($run->campaign_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== DrawCampaignStatusEnum::DRAWING) {
                throw new DrawRunExecutionException('Draw campaign is not frozen in DRAWING status.');
            }

            if ($run->algorithm !== DrawAlgorithm::NAME) {
                throw new DrawRunExecutionException('Unsupported draw algorithm version.');
            }

            if (!$run->seed_ciphertext || !$run->seed_commitment) {
                throw new DrawRunExecutionException('Draw run does not contain a recoverable committed seed.');
            }

            $metadata = $run->metadata ?? [];
            $attempts = is_array($metadata['attempts'] ?? null) ? $metadata['attempts'] : [];
            $attempts[] = [
                'status' => 'running',
                'started_at' => now()->toImmutable()->toIso8601String(),
            ];
            $metadata['attempts'] = $attempts;

            $run->forceFill([
                'status' => DrawRunStatusEnum::RUNNING,
                'started_at' => $run->started_at ?? now(),
                'failed_at' => null,
                'failure_reason' => null,
                'metadata' => $metadata,
            ])->save();

            return $run->refresh();
        }, 3);

        if ($run->status === DrawRunStatusEnum::COMPLETED) {
            return $run;
        }

        $campaign = DrawCampaign::query()->findOrFail($run->campaign_id);
        $metadata = $run->metadata ?? [];
        $prizePlan = $metadata['prize_plan'] ?? null;
        $candidateLimit = (int)($metadata['candidate_limit'] ?? 0);

        if (!is_array($prizePlan) || $prizePlan === [] || $candidateLimit <= 0) {
            throw new DrawRunExecutionException('Draw run prize plan snapshot is missing or invalid.');
        }

        $seed = $this->seedManager->decrypt((string)$run->seed_ciphertext);

        if (!hash_equals((string)$run->seed_commitment, $this->seedManager->commitment($seed))) {
            throw new DrawRunExecutionException('Stored draw seed does not match its commitment.');
        }

        $snapshot = $this->candidateSelector->select(
            campaign: $campaign,
            seed: $seed,
            candidateLimit: $candidateLimit,
            allowMultipleWinsPerUser: (bool)($metadata['allow_multiple_wins_per_user'] ?? false),
        );

        $selections = $this->winnerPlanner->plan(
            prizePlan: $prizePlan,
            candidates: $snapshot->candidates,
        );

        return DB::transaction(function () use ($run, $campaign, $seed, $snapshot, $selections, $prizePlan): DrawRun {
            $lockedRun = DrawRun::query()->whereKey($run->getKey())->lockForUpdate()->firstOrFail();
            $lockedCampaign = DrawCampaign::query()->whereKey($campaign->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedRun->status === DrawRunStatusEnum::COMPLETED) {
                return $lockedRun;
            }

            if ($lockedRun->status !== DrawRunStatusEnum::RUNNING) {
                throw new DrawRunExecutionException('Draw run is no longer in RUNNING status.');
            }

            if ($lockedCampaign->status !== DrawCampaignStatusEnum::DRAWING) {
                throw new DrawRunExecutionException('Draw campaign is no longer frozen for drawing.');
            }

            if ($lockedRun->winners()->exists()) {
                throw new DrawRunExecutionException('Winner records already exist for this draw run.');
            }

            $selectedAt = now()->toImmutable();
            $winnerIds = [];

            foreach ($selections as $selection) {
                $winner = DrawWinner::query()->create([
                    'campaign_id' => $lockedCampaign->getKey(),
                    'draw_run_id' => $lockedRun->getKey(),
                    'prize_id' => $selection->prizeId,
                    'ticket_id' => $selection->candidate->ticketId,
                    'user_id' => $selection->candidate->userId,
                    'kind' => $selection->kind,
                    'position' => $selection->position,
                    'status' => DrawWinnerStatusEnum::PENDING,
                    'selected_at' => $selectedAt,
                    'metadata' => [
                        'score' => $selection->candidate->score,
                        'selection_order' => $selection->selectionOrder,
                        'ticket_code_snapshot' => $selection->candidate->ticketCode,
                        'algorithm' => DrawAlgorithm::NAME,
                    ],
                ]);

                $winnerIds[] = (int)$winner->getKey();
            }

            $runMetadata = $lockedRun->metadata ?? [];
            $primaryFilled = count(array_filter(
                $selections,
                fn($selection): bool => $selection->kind->value === 'primary',
            ));
            $reserveFilled = count($selections) - $primaryFilled;
            $runMetadata['result'] = [
                'primary_slots_filled' => $primaryFilled,
                'reserve_slots_filled' => $reserveFilled,
                'winner_records_count' => count($winnerIds),
                'prize_plan' => $prizePlan,
            ];

            $lockedRun->forceFill([
                'status' => DrawRunStatusEnum::COMPLETED,
                'eligible_ticket_count' => $snapshot->eligibleTicketCount,
                'eligible_set_hash' => $snapshot->eligibleSetHash,
                'seed_reveal' => $this->seedManager->reveal($seed),
                'completed_at' => $selectedAt,
                'failed_at' => null,
                'failure_reason' => null,
                'metadata' => $runMetadata,
            ])->save();

            $from = $lockedCampaign->status;
            $lockedCampaign->forceFill([
                'status' => DrawCampaignStatusEnum::COMPLETED,
                'completed_at' => $selectedAt,
            ])->save();

            DB::afterCommit(function () use ($lockedCampaign, $lockedRun, $winnerIds, $snapshot, $selectedAt, $from): void {
                event(new DrawCampaignStatusChangedEvent(
                    campaignId: (int)$lockedCampaign->getKey(),
                    from: $from,
                    to: DrawCampaignStatusEnum::COMPLETED,
                    actorUserId: $lockedRun->started_by_user_id,
                    occurredAt: $selectedAt,
                    reason: 'draw_completed',
                ));

                event(new DrawWinnersSelectedEvent(
                    campaignId: (int)$lockedCampaign->getKey(),
                    drawRunId: (int)$lockedRun->getKey(),
                    winnerIds: $winnerIds,
                    occurredAt: $selectedAt,
                ));

                event(new DrawCompletedEvent(
                    campaignId: (int)$lockedCampaign->getKey(),
                    drawRunId: (int)$lockedRun->getKey(),
                    eligibleTicketCount: $snapshot->eligibleTicketCount,
                    eligibleSetHash: $snapshot->eligibleSetHash,
                    occurredAt: $selectedAt,
                ));
            });

            return $lockedRun->refresh();
        }, 3);
    }
}
