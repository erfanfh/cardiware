<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleProductBulkAction
{
    /**
     * @param string $action
     * @param array $ids
     * @return BulkActionResultDTO
     * @throws Throwable
     */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids) {
            $affectedCount = match ($action) {
                'activate' => $this->updateStatus($ids, ProductStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, ProductStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, ProductStatusEnum $status): int
    {
        $count = 0;
        foreach (Product::query()->whereIn('id', $ids)->get() as $product) {
            if ($product->status !== $status) {
                $product->update(['status' => $status]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param list<int> $ids
     */
    protected function delete(array $ids): int
    {
        $items = Product::query()
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
            'activate' => "{$result->affectedCount} محصول با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} محصول با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} محصول با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
