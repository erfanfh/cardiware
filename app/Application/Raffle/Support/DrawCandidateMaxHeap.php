<?php

namespace App\Application\Raffle\Support;

use SplPriorityQueue;

class DrawCandidateMaxHeap extends SplPriorityQueue
{
    public function compare(mixed $priority1, mixed $priority2): int
    {
        $score = strcmp((string)$priority1['score'], (string)$priority2['score']);

        if ($score !== 0) {
            return $score;
        }

        return ((int)$priority1['ticket_id']) <=> ((int)$priority2['ticket_id']);
    }
}
