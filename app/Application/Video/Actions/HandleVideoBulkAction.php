<?php

namespace App\Application\Video\Actions;

use App\Domain\Video\Enums\VideoStatusEnum;
use App\Domain\Video\Models\Video;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HandleVideoBulkAction
{
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids): BulkActionResultDTO {
            $count = match ($action) {
                'activate' => $this->updateStatus($ids, VideoStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, VideoStatusEnum::INACTIVE),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO($action, $count);
        });
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} ویدیو با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} ویدیو با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} ویدیو با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد بروزرسانی شد.",
        };
    }

    private function updateStatus(array $ids, VideoStatusEnum $status): int
    {
        $count = 0;
        foreach (Video::query()->whereIn('id', $ids)->get() as $video) {
            if ($video->status !== $status) {
                $video->update(['status' => $status]);
                $count++;
            }
        }

        return $count;
    }

    private function delete(array $ids): int
    {
        $videos = Video::query()->whereIn('id', $ids)->get();
        $videos->each->delete();

        return $videos->count();
    }
}
