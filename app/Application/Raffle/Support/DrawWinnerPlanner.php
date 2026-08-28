<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\DTOs\DrawCandidateDTO;
use App\Application\Raffle\DTOs\DrawWinnerSelectionDTO;
use App\Application\Raffle\Exceptions\InsufficientEligibleDrawCandidatesException;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;

class DrawWinnerPlanner
{
    /**
     * @param list<array{prize_id:int,title:string,quantity:int,reserve_count:int,sort_order:int}> $prizePlan
     * @param list<DrawCandidateDTO> $candidates
     * @return list<DrawWinnerSelectionDTO>
     */
    public function plan(array $prizePlan, array $candidates): array
    {
        $requiredPrimary = array_sum(array_column($prizePlan, 'quantity'));

        if (count($candidates) < $requiredPrimary) {
            throw new InsufficientEligibleDrawCandidatesException(
                'Eligible draw candidates are not enough to fill all primary prize slots.'
            );
        }

        $cursor = 0;
        $selectionOrder = 0;
        $selections = [];

        foreach ($prizePlan as $prize) {
            for ($position = 1; $position <= (int)$prize['quantity']; ++$position) {
                $selections[] = new DrawWinnerSelectionDTO(
                    prizeId: (int)$prize['prize_id'],
                    kind: DrawWinnerKindEnum::PRIMARY,
                    position: $position,
                    candidate: $candidates[$cursor++],
                    selectionOrder: ++$selectionOrder,
                );
            }
        }

        foreach ($prizePlan as $prize) {
            for ($position = 1; $position <= (int)$prize['reserve_count']; ++$position) {
                if (!isset($candidates[$cursor])) {
                    break 2;
                }

                $selections[] = new DrawWinnerSelectionDTO(
                    prizeId: (int)$prize['prize_id'],
                    kind: DrawWinnerKindEnum::RESERVE,
                    position: $position,
                    candidate: $candidates[$cursor++],
                    selectionOrder: ++$selectionOrder,
                );
            }
        }

        return $selections;
    }
}
