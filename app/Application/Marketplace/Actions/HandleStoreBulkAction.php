<?php

namespace App\Application\Marketplace\Actions;

use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleStoreBulkAction
{
    /**
     * @param string $action
     * @param list<int> $ids
     * @return BulkActionResultDTO
     * @throws Throwable
     */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids) {
            $affectedCount = match ($action) {
                'activate' => $this->updateStatus($ids, StoreStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, StoreStatusEnum::INACTIVE),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO(
                action: $action,
                affectedCount: $affectedCount,
            );
        });
    }

    /**
     * @param list<int> $ids
     */
    protected function updateStatus(array $ids, StoreStatusEnum $status): int
    {
        return Store::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => $status,
            ]);
    }

    /**
     * @param list<int> $ids
     */
    protected function delete(array $ids): int
    {
        $items = Store::query()
            ->whereIn('id', $ids)
            ->get();

        $count = $items->count();

        foreach ($items as $item) {
            $item->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} فروشنده با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} فروشنده با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} فروشنده با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
