<?php

namespace App\Application\Identity\Actions\User;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleUserBulkAction
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
                'activate' => $this->updateStatus($ids, UserStatusEnum::ACTIVE),
                'deactivate' => $this->updateStatus($ids, UserStatusEnum::INACTIVE),
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
    protected function updateStatus(array $ids, UserStatusEnum $status): int
    {
        $count = 0;
        foreach (User::query()->whereIn('id', $ids)->get() as $user) {
            if ($user->status !== $status) {
                $user->update(['status' => $status]);
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
        $users = User::query()
            ->whereIn('id', $ids)
            ->get();

        $count = $users->count();

        foreach ($users as $user) {
            $user->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} کاربر با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} کاربر با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} کاربر با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
