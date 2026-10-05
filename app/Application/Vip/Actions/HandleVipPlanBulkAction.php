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
                'activate' => VipPlan::query()->whereIn('id', $ids)->update(['is_active' => true]),
                'deactivate' => VipPlan::query()->whereIn('id', $ids)->update(['is_active' => false]),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO(action: $action, affectedCount: $count);
        });
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
