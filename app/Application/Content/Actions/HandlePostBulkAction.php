<?php

namespace App\Application\Content\Actions;

use App\Application\Content\Support\PostEditLock;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandlePostBulkAction
{
    public function __construct(private readonly PostEditLock $locks) {}

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
            foreach (Post::query()->whereIn('id', $ids)->orderBy('id')->get() as $post) {
                $this->locks->withoutEditor($post, fn () => null);
            }

            $affectedCount = match ($action) {
                'activate' => $this->updateStatus($ids, PostStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, PostStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, PostStatusEnum $status): int
    {
        $count = 0;
        foreach (Post::query()->whereIn('id', $ids)->get() as $post) {
            if ($post->status !== $status) {
                $post->update(['status' => $status]);
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
        $posts = Post::query()
            ->whereIn('id', $ids)
            ->get();

        $count = $posts->count();

        foreach ($posts as $post) {
            $post->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} نوشته با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} نوشته با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} نوشته با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
