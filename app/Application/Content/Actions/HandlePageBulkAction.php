<?php

namespace App\Application\Content\Actions;

use App\Domain\Content\Enums\PageStatusEnum;
use App\Domain\Content\Models\Page;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandlePageBulkAction
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
                'activate' => $this->updateStatus($ids, PageStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, PageStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, PageStatusEnum $status): int
    {
        return Page::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => $status
            ]);
    }

    /**
     * @param list<int> $ids
     */
    protected function delete(array $ids): int
    {
        $pages = Page::query()
            ->whereIn('id', $ids)
            ->get();

        $count = $pages->count();

        foreach ($pages as $page) {
            $page->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} برگه با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} برگه با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} برگه با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
