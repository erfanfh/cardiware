<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\DTOs\DrawCandidateDTO;
use App\Application\Raffle\DTOs\DrawSelectionSnapshotDTO;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\DB;

class DrawCandidateSelector
{
    public function __construct(
        private readonly DrawAlgorithm $algorithm,
        private readonly DrawCandidateComparator $comparator,
    ) {
    }

    public function select(
        DrawCampaign $campaign,
        string $seed,
        int $candidateLimit,
        ?bool $allowMultipleWinsPerUser = null,
        array $excludedTicketIds = [],
        array $excludedUserIds = [],
    ): DrawSelectionSnapshotDTO {
        $allowMultipleWinsPerUser ??= (bool)$campaign->allow_multiple_wins_per_user;
        $hashContext = hash_init('sha256');
        $eligibleCount = 0;

        $ticketHeap = new DrawCandidateMaxHeap();
        $ticketHeap->setExtractFlags(DrawCandidateMaxHeap::EXTR_DATA);

        $uniqueUserHeap = new DrawCandidateMaxHeap();
        $uniqueUserHeap->setExtractFlags(DrawCandidateMaxHeap::EXTR_DATA);
        /** @var array<int, DrawCandidateDTO> $bestByUser */
        $bestByUser = [];

        $query = DB::table('draw_tickets')
            ->where('campaign_id', $campaign->getKey())
            ->where('status', DrawTicketStatusEnum::ACTIVE->value);

        if ($excludedTicketIds !== []) {
            $query->whereNotIn('id', $excludedTicketIds);
        }

        if ($excludedUserIds !== []) {
            $query->whereNotIn('user_id', $excludedUserIds);
        }

        $query
            ->select(['id', 'public_token', 'user_id', 'code'])
            ->orderBy('id')
            ->chunkById(5000, function ($tickets) use (
                $campaign,
                $seed,
                $candidateLimit,
                $hashContext,
                &$eligibleCount,
                $ticketHeap,
                $uniqueUserHeap,
                &$bestByUser,
            ): void {
                foreach ($tickets as $ticket) {
                    ++$eligibleCount;

                    hash_update(
                        $hashContext,
                        (int)$ticket->id . "\x1F"
                        . (string)$ticket->public_token . "\x1F"
                        . (int)$ticket->user_id . "\x1F"
                        . (string)$ticket->code . "\n",
                    );

                    if ($candidateLimit <= 0) {
                        continue;
                    }

                    $candidate = new DrawCandidateDTO(
                        ticketId: (int)$ticket->id,
                        userId: (int)$ticket->user_id,
                        ticketPublicToken: (string)$ticket->public_token,
                        ticketCode: (string)$ticket->code,
                        score: $this->algorithm->score(
                            seed: $seed,
                            campaignPublicToken: (string)$campaign->public_token,
                            ticketPublicToken: (string)$ticket->public_token,
                        ),
                    );

                    if ($allowMultipleWinsPerUser) {
                        $this->retainTopTickets($ticketHeap, $candidate, $candidateLimit);
                    } else {
                        $this->retainTopUniqueUsers(
                            heap: $uniqueUserHeap,
                            bestByUser: $bestByUser,
                            candidate: $candidate,
                            limit: $candidateLimit,
                        );
                    }
                }
            }, column: 'id');

        $candidates = $allowMultipleWinsPerUser
            ? $this->heapCandidates($ticketHeap)
            : array_values($bestByUser);

        usort($candidates, fn(DrawCandidateDTO $a, DrawCandidateDTO $b): int => $this->comparator->compare($a, $b));

        return new DrawSelectionSnapshotDTO(
            eligibleTicketCount: $eligibleCount,
            eligibleSetHash: hash_final($hashContext),
            candidates: $candidates,
        );
    }

    private function retainTopTickets(
        DrawCandidateMaxHeap $heap,
        DrawCandidateDTO $candidate,
        int $limit,
    ): void {
        if ($heap->count() < $limit) {
            $this->insert($heap, $candidate);
            return;
        }

        $heap->top();
        /** @var DrawCandidateDTO $worst */
        $worst = $heap->current();

        if ($this->comparator->isBetter($candidate, $worst)) {
            $heap->extract();
            $this->insert($heap, $candidate);
        }
    }

    /**
     * @param array<int, DrawCandidateDTO> $bestByUser
     */
    private function retainTopUniqueUsers(
        DrawCandidateMaxHeap $heap,
        array &$bestByUser,
        DrawCandidateDTO $candidate,
        int $limit,
    ): void {
        $existing = $bestByUser[$candidate->userId] ?? null;

        if ($existing) {
            if ($this->comparator->isBetter($candidate, $existing)) {
                $bestByUser[$candidate->userId] = $candidate;
                $this->insert($heap, $candidate);
            }
            return;
        }

        if (count($bestByUser) < $limit) {
            $bestByUser[$candidate->userId] = $candidate;
            $this->insert($heap, $candidate);
            return;
        }

        $this->pruneStaleTop($heap, $bestByUser);

        if ($heap->isEmpty()) {
            $bestByUser[$candidate->userId] = $candidate;
            $this->insert($heap, $candidate);
            return;
        }

        $heap->top();
        /** @var DrawCandidateDTO $worst */
        $worst = $heap->current();

        if (!$this->comparator->isBetter($candidate, $worst)) {
            return;
        }

        $heap->extract();
        unset($bestByUser[$worst->userId]);
        $bestByUser[$candidate->userId] = $candidate;
        $this->insert($heap, $candidate);
    }

    /**
     * @param array<int, DrawCandidateDTO> $bestByUser
     */
    private function pruneStaleTop(DrawCandidateMaxHeap $heap, array $bestByUser): void
    {
        while (!$heap->isEmpty()) {
            $heap->top();
            /** @var DrawCandidateDTO $candidate */
            $candidate = $heap->current();
            $current = $bestByUser[$candidate->userId] ?? null;

            if (
                $current
                && $current->ticketId === $candidate->ticketId
                && hash_equals($current->score, $candidate->score)
            ) {
                return;
            }

            $heap->extract();
        }
    }

    private function insert(DrawCandidateMaxHeap $heap, DrawCandidateDTO $candidate): void
    {
        $heap->insert($candidate, [
            'score' => $candidate->score,
            'ticket_id' => $candidate->ticketId,
        ]);
    }

    /** @return list<DrawCandidateDTO> */
    private function heapCandidates(DrawCandidateMaxHeap $heap): array
    {
        $clone = clone $heap;
        $clone->setExtractFlags(DrawCandidateMaxHeap::EXTR_DATA);
        $items = [];

        while (!$clone->isEmpty()) {
            $items[] = $clone->extract();
        }

        return $items;
    }
}
