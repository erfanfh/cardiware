<?php

namespace App\Application\Promotion\Actions\Coupon;

use Closure;
use Illuminate\Support\Collection;

class AllocateCouponDiscountAcrossItemsAction
{
    /**
     * @param Collection<int, mixed> $items
     * @return array<int, int>
     */
    public function execute(Collection $items, int $discountAmount, Closure $eligibleAmountResolver): array
    {
        $discountAmount = max(0, $discountAmount);

        if ($discountAmount <= 0 || $items->isEmpty()) {
            return [];
        }

        $rows = $items
            ->map(function ($item) use ($eligibleAmountResolver): array {
                return [
                    'id' => (int)$item->id,
                    'amount' => max(0, (int)$eligibleAmountResolver($item)),
                ];
            })
            ->filter(fn(array $row): bool => $row['id'] > 0 && $row['amount'] > 0)
            ->values();

        $eligibleAmount = (int)$rows->sum('amount');

        if ($eligibleAmount <= 0) {
            return [];
        }

        $discountAmount = min($discountAmount, $eligibleAmount);

        $allocations = [];
        $allocatedAmount = 0;

        foreach ($rows as $row) {
            $itemDiscount = intdiv($discountAmount * $row['amount'], $eligibleAmount);

            $allocations[$row['id']] = $itemDiscount;
            $allocatedAmount += $itemDiscount;
        }

        $remainder = $discountAmount - $allocatedAmount;

        if ($remainder > 0) {
            $rows = $rows
                ->sortByDesc('amount')
                ->values();

            foreach ($rows as $row) {
                if ($remainder <= 0) {
                    break;
                }

                $allocations[$row['id']] = ($allocations[$row['id']] ?? 0) + 1;
                $remainder--;
            }
        }

        return $allocations;
    }
}
