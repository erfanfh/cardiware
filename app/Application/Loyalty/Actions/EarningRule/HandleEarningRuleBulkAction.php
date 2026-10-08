<?php

namespace App\Application\Loyalty\Actions\EarningRule;

use App\Domain\Loyalty\Models\EarningRule;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleEarningRuleBulkAction
{
    /**
     * @param list<int> $ids
     * @throws Throwable
     */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids): BulkActionResultDTO {
            $affectedCount = match ($action) {
                'activate' => $this->updateActiveState($ids, true),
                'deactivate' => $this->updateActiveState($ids, false),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO(
                action: $action,
                affectedCount: $affectedCount,
            );
        });
    }

    /** @param list<int> $ids */
    private function updateActiveState(array $ids, bool $isActive): int
    {
        $count = 0;
        foreach (EarningRule::query()->whereIn('id', $ids)->get() as $rule) {
            if ((bool) $rule->is_active !== $isActive) {
                $rule->update(['is_active' => $isActive]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param list<int> $ids
     * @throws Throwable
     */
    private function delete(array $ids): int
    {
        $rules = EarningRule::query()->whereIn('id', $ids)->get();
        $count = $rules->count();

        foreach ($rules as $rule) {
            app(DeleteEarningRuleAction::class)->execute($rule);
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} قانون امتیاز با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} قانون امتیاز با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} قانون امتیاز با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت بروزرسانی شد.",
        };
    }
}
