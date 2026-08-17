<?php

namespace App\Application\Raffle\DTOs;

final readonly class DrawCandidateDTO
{
    public function __construct(
        public int $ticketId,
        public int $userId,
        public string $ticketPublicToken,
        public string $ticketCode,
        public string $score,
    ) {
    }
}
