<?php

namespace App\Application\Raffle\DTOs;

final readonly class DrawSelectionSnapshotDTO
{
    /**
     * @param list<DrawCandidateDTO> $candidates
     */
    public function __construct(
        public int $eligibleTicketCount,
        public string $eligibleSetHash,
        public array $candidates,
    ) {
    }
}
