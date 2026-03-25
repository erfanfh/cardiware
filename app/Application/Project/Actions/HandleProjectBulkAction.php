<?php

namespace App\Application\Project\Actions;

use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleProjectBulkAction
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
                'activate' => $this->updateStatus($ids, ProjectStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, ProjectStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, ProjectStatusEnum $status): int
    {
        return Project::query()
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
        $projects = Project::query()
            ->whereIn('id', $ids)
            ->get();

        $count = $projects->count();

        foreach ($projects as $project) {
            $project->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} پروژه با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} پروژه با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} پروژه با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
