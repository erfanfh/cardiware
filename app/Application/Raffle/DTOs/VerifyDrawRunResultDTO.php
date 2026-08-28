<?php

namespace App\Application\Raffle\DTOs;

final readonly class VerifyDrawRunResultDTO
{
    /** @param list<string> $errors */
    public function __construct(
        public bool $valid,
        public bool $seedCommitmentValid,
        public bool $eligibleSetHashValid,
        public bool $eligibleTicketCountValid,
        public bool $winnerSetValid,
        public array $errors = [],
    ) {
    }
}
