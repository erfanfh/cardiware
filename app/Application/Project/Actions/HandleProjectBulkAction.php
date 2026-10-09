<?php

namespace App\Application\Project\Actions;

use App\Application\Project\Support\ProjectEditLock;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleProjectBulkAction
{
    public function __construct(private readonly ProjectEditLock $locks) {}

    /**
     * @param string $action
     * @param array $ids
     * @return BulkActionResultDTO
     * @throws Throwable
     */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids) {
            // Keep every row locked until the entire bulk action commits or rolls back.
            foreach (Project::query()->whereIn('id', $ids)->orderBy('id')->get() as $project) {
                $this->locks->withoutEditor($project, fn () => null);
            }

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
        $count = 0;
        foreach (Project::query()->whereIn('id', $ids)->get() as $project) {
            if ($project->status !== $status) {
                $project->update(['status' => $status]);
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
