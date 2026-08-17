<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Application\Raffle\DTOs\VerifyDrawRunResultDTO;
use App\Application\Raffle\Support\DrawAlgorithm;
use App\Application\Raffle\Support\DrawCandidateSelector;
use App\Application\Raffle\Support\DrawRunSeedManager;
use App\Application\Raffle\Support\DrawWinnerPlanner;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Models\DrawRun;

class VerifyDrawRunAction
{
    public function __construct(
        private readonly DrawRunSeedManager $seedManager,
        private readonly DrawCandidateSelector $candidateSelector,
        private readonly DrawWinnerPlanner $winnerPlanner,
    ) {
    }

    public function execute(DrawRun $drawRun): VerifyDrawRunResultDTO
    {
        $run = DrawRun::query()
            ->with(['campaign', 'winners'])
            ->findOrFail($drawRun->getKey());

        $errors = [];

        if ($run->status !== DrawRunStatusEnum::COMPLETED || !$run->seed_reveal) {
            return new VerifyDrawRunResultDTO(false, false, false, false, false, [
                'Draw run is not completed or its seed has not been revealed.',
            ]);
        }

        if ($run->algorithm !== DrawAlgorithm::NAME) {
            return new VerifyDrawRunResultDTO(false, false, false, false, false, [
                'Unsupported draw algorithm version.',
            ]);
        }

        $seed = $this->seedManager->fromReveal((string)$run->seed_reveal);
        $seedValid = hash_equals((string)$run->seed_commitment, $this->seedManager->commitment($seed));
        if (!$seedValid) {
            $errors[] = 'Seed reveal does not match the original commitment.';
        }

        $metadata = $run->metadata ?? [];
        $prizePlan = $metadata['prize_plan'] ?? null;
        $candidateLimit = (int)($metadata['candidate_limit'] ?? 0);

        if (!is_array($prizePlan) || !$prizePlan || $candidateLimit <= 0) {
            return new VerifyDrawRunResultDTO(false, $seedValid, false, false, false, array_merge($errors, [
                'Draw run prize snapshot is missing.',
            ]));
        }

        $snapshot = $this->candidateSelector->select(
            campaign: $run->campaign,
            seed: $seed,
            candidateLimit: $candidateLimit,
            allowMultipleWinsPerUser: (bool)($metadata['allow_multiple_wins_per_user'] ?? false),
        );

        $countValid = $snapshot->eligibleTicketCount === (int)$run->eligible_ticket_count;
        $hashValid = hash_equals((string)$run->eligible_set_hash, $snapshot->eligibleSetHash);

        if (!$countValid) {
            $errors[] = 'Eligible ticket count no longer matches the recorded draw snapshot.';
        }
        if (!$hashValid) {
            $errors[] = 'Eligible ticket set hash no longer matches the recorded draw snapshot.';
        }

        $expected = $this->winnerPlanner->plan($prizePlan, $snapshot->candidates);
        $actual = $run->winners
            ->map(fn($winner): array => [
                'prize_id' => (int)$winner->prize_id,
                'kind' => $winner->kind->value,
                'position' => (int)$winner->position,
                'ticket_id' => (int)$winner->ticket_id,
                'user_id' => (int)$winner->user_id,
                'score' => (string)($winner->metadata['score'] ?? ''),
            ])
            ->sortBy(fn(array $item): string => sprintf('%010d|%s|%010d', $item['prize_id'], $item['kind'], $item['position']))
            ->values()
            ->all();

        $expectedComparable = collect($expected)
            ->map(fn($selection): array => [
                'prize_id' => $selection->prizeId,
                'kind' => $selection->kind->value,
                'position' => $selection->position,
                'ticket_id' => $selection->candidate->ticketId,
                'user_id' => $selection->candidate->userId,
                'score' => $selection->candidate->score,
            ])
            ->sortBy(fn(array $item): string => sprintf('%010d|%s|%010d', $item['prize_id'], $item['kind'], $item['position']))
            ->values()
            ->all();

        $winnerSetValid = $actual === $expectedComparable;
        if (!$winnerSetValid) {
            $errors[] = 'Persisted winners do not match the deterministic draw result.';
        }

        return new VerifyDrawRunResultDTO(
            valid: $seedValid && $hashValid && $countValid && $winnerSetValid,
            seedCommitmentValid: $seedValid,
            eligibleSetHashValid: $hashValid,
            eligibleTicketCountValid: $countValid,
            winnerSetValid: $winnerSetValid,
            errors: $errors,
        );
    }
}
