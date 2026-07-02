<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleCouponBulkAction
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
                'activate' => $this->updateStatus($ids, CouponStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, CouponStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, CouponStatusEnum $status): int
    {
        return Coupon::query()
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
        $items = Coupon::query()
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
            'activate' => "{$result->affectedCount} کدتخفیف با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} کدتخفیف با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} کدتخفیف با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
