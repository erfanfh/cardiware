<?php

namespace App\Application\Raffle\Support;

use App\Domain\Raffle\Models\DrawCampaign;

class DrawPrizePlanFactory
{
    /**
     * @return list<array{prize_id:int,title:string,quantity:int,reserve_count:int,sort_order:int}>
     */
    public function fromCampaign(DrawCampaign $campaign): array
    {
        return $campaign->prizes()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn($prize): array => [
                'prize_id' => (int)$prize->getKey(),
                'title' => (string)$prize->title,
                'quantity' => (int)$prize->quantity,
                'reserve_count' => (int)$prize->effectiveReserveWinnersCount(),
                'sort_order' => (int)$prize->sort_order,
            ])
            ->values()
            ->all();
    }

    /** @param list<array{quantity:int}> $plan */
    public function primarySlots(array $plan): int
    {
        return array_sum(array_map(fn(array $prize): int => (int)$prize['quantity'], $plan));
    }

    /** @param list<array{reserve_count:int}> $plan */
    public function reserveSlots(array $plan): int
    {
        return array_sum(array_map(fn(array $prize): int => (int)$prize['reserve_count'], $plan));
    }
}
