<?php

namespace App\Application\Raffle\DTOs;

use App\Domain\Raffle\Enums\DrawWinnerKindEnum;

final readonly class DrawWinnerSelectionDTO
{
    public function __construct(
        public int $prizeId,
        public DrawWinnerKindEnum $kind,
        public int $position,
        public DrawCandidateDTO $candidate,
        public int $selectionOrder,
    ) {
    }
}
