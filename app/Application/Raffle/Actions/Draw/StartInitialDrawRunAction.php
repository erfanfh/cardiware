<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Application\Raffle\Exceptions\DrawStartNotAllowedException;
use App\Application\Raffle\Jobs\ExecuteDrawRunJob;
use App\Application\Raffle\Support\DrawAlgorithm;
use App\Application\Raffle\Support\DrawCampaignStatusTransitionGuard;
use App\Application\Raffle\Support\DrawPrizePlanFactory;
use App\Application\Raffle\Support\DrawRunSeedManager;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawRunTypeEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Events\DrawCampaignStatusChangedEvent;
use App\Domain\Raffle\Events\DrawStartedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartInitialDrawRunAction
{
    public function __construct(
        private readonly DrawCampaignStatusTransitionGuard $transitionGuard,
        private readonly DrawPrizePlanFactory $prizePlanFactory,
        private readonly DrawRunSeedManager $seedManager,
    ) {
    }

    /** @throws Throwable */
    public function execute(DrawCampaign $campaign, ?User $actor = null): DrawRun
    {
        if (!$campaign->exists) {
            throw new DrawStartNotAllowedException('Draw campaign must exist before drawing can start.');
        }

        if ($actor && !$actor->exists) {
            throw new DrawStartNotAllowedException('Draw starter user must exist.');
        }

        return DB::transaction(function () use ($campaign, $actor): DrawRun {
            $campaign = DrawCampaign::query()
                ->whereKey($campaign->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== DrawCampaignStatusEnum::CLOSED) {
                throw new DrawStartNotAllowedException('Only a closed draw campaign can start drawing.');
            }

            $now = now()->toImmutable();

            if ($campaign->draw_at && $now->lt($campaign->draw_at)) {
                throw new DrawStartNotAllowedException('Draw cannot start before its configured draw time.');
            }

            if ($campaign->drawRuns()->where('type', DrawRunTypeEnum::INITIAL)->exists()) {
                throw new DrawStartNotAllowedException(
                    'This campaign already has an initial draw run. Retry the existing run instead of creating a new seed.'
                );
            }

            $this->transitionGuard->ensureAllowed(
                campaign: $campaign,
                targetStatus: DrawCampaignStatusEnum::DRAWING,
                occurredAt: $now,
            );

            $prizePlan = $this->prizePlanFactory->fromCampaign($campaign);
            $primarySlots = $this->prizePlanFactory->primarySlots($prizePlan);
            $reserveSlots = $this->prizePlanFactory->reserveSlots($prizePlan);

            if ($primarySlots <= 0) {
                throw new DrawStartNotAllowedException('Draw campaign has no primary prize slots.');
            }

            $activeTicketsQuery = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('status', DrawTicketStatusEnum::ACTIVE);

            $eligibleTicketCount = (clone $activeTicketsQuery)->count();
            $eligibleCandidateCount = $campaign->allow_multiple_wins_per_user
                ? $eligibleTicketCount
                : (clone $activeTicketsQuery)->distinct()->count('user_id');

            if ($eligibleCandidateCount < $primarySlots) {
                throw new DrawStartNotAllowedException(
                    $campaign->allow_multiple_wins_per_user
                        ? 'Active tickets are not enough to fill all primary prizes.'
                        : 'Distinct eligible participants are not enough to fill all primary prizes while multiple wins are disabled.'
                );
            }

            $candidateLimit = min(
                $eligibleCandidateCount,
                $primarySlots + $reserveSlots,
            );

            $seed = $this->seedManager->generate();
            $seedCommitment = $this->seedManager->commitment($seed);

            $run = DrawRun::query()->create([
                'campaign_id' => $campaign->getKey(),
                'type' => DrawRunTypeEnum::INITIAL,
                'status' => DrawRunStatusEnum::PENDING,
                'algorithm' => DrawAlgorithm::NAME,
                'seed_commitment' => $seedCommitment,
                'seed_ciphertext' => $this->seedManager->encrypt($seed),
                'started_by_user_id' => $actor?->getKey(),
                'metadata' => [
                    'prize_plan' => $prizePlan,
                    'primary_slots' => $primarySlots,
                    'reserve_slots_requested' => $reserveSlots,
                    'candidate_limit' => $candidateLimit,
                    'eligible_candidates_preflight' => $eligibleCandidateCount,
                    'eligible_tickets_preflight' => $eligibleTicketCount,
                    'allow_multiple_wins_per_user' => (bool)$campaign->allow_multiple_wins_per_user,
                    'created_at' => $now->toIso8601String(),
                    'attempts' => [],
                ],
            ]);

            $from = $campaign->status;
            $campaign->forceFill([
                'status' => DrawCampaignStatusEnum::DRAWING,
                'drawing_started_at' => $now,
            ])->save();

            DB::afterCommit(function () use ($campaign, $run, $actor, $from, $now, $seedCommitment): void {
                event(new DrawCampaignStatusChangedEvent(
                    campaignId: (int)$campaign->getKey(),
                    from: $from,
                    to: DrawCampaignStatusEnum::DRAWING,
                    actorUserId: $actor?->getKey(),
                    occurredAt: $now,
                    reason: 'draw_started',
                ));

                event(new DrawStartedEvent(
                    campaignId: (int)$campaign->getKey(),
                    drawRunId: (int)$run->getKey(),
                    actorUserId: $actor?->getKey(),
                    seedCommitment: $seedCommitment,
                    occurredAt: $now,
                ));

                ExecuteDrawRunJob::dispatch((int)$run->getKey());
            });

            return $run->refresh();
        }, 3);
    }
}
