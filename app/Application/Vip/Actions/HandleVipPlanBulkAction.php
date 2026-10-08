<?php

namespace App\Application\Vip\Actions;

use App\Domain\Vip\Models\VipPlan;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HandleVipPlanBulkAction
{
    /** @param list<int> $ids */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids): BulkActionResultDTO {
            $count = match ($action) {
                'activate' => $this->updateActive($ids, true),
                'deactivate' => $this->updateActive($ids, false),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO(action: $action, affectedCount: $count);
        });
    }

    /** @param list<int> $ids */
    private function updateActive(array $ids, bool $active): int
    {
        $count = 0;
        foreach (VipPlan::query()->whereIn('id', $ids)->get() as $plan) {
            if ((bool) $plan->is_active !== $active) {
                $plan->update(['is_active' => $active]);
                $count++;
            }
        }

        return $count;
    }

    /** @param list<int> $ids */
    private function delete(array $ids): int
    {
        $plans = VipPlan::query()->whereIn('id', $ids)->get();

        foreach ($plans as $plan) {
            $plan->delete();
        }

        return $plans->count();
    }
}
