<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\DTOs\DrawCandidateDTO;

class DrawCandidateComparator
{
    /** Negative means $left is better (ranks earlier). */
    public function compare(DrawCandidateDTO $left, DrawCandidateDTO $right): int
    {
        $score = strcmp($left->score, $right->score);

        if ($score !== 0) {
            return $score;
        }

        return $left->ticketId <=> $right->ticketId;
    }

    public function isBetter(DrawCandidateDTO $left, DrawCandidateDTO $right): bool
    {
        return $this->compare($left, $right) < 0;
    }
}
