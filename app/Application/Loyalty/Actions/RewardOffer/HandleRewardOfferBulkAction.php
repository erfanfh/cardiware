<?php

namespace App\Application\Loyalty\Actions\RewardOffer;

use App\Domain\Loyalty\Models\RewardOffer;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleRewardOfferBulkAction
{
    /** @param list<int> $ids @throws Throwable */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        return DB::transaction(function () use ($action, $ids): BulkActionResultDTO {
            $affectedCount = match ($action) {
                'activate' => $this->updateActiveState($ids, true),
                'deactivate' => $this->updateActiveState($ids, false),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException("Unsupported bulk action [{$action}]"),
            };

            return new BulkActionResultDTO(action: $action, affectedCount: $affectedCount);
        });
    }

    /** @param list<int> $ids */
    private function updateActiveState(array $ids, bool $isActive): int
    {
        return RewardOffer::query()
            ->whereIn('id', $ids)
            ->update(['is_active' => $isActive]);
    }

    /** @param list<int> $ids @throws Throwable */
    private function delete(array $ids): int
    {
        $offers = RewardOffer::query()->whereIn('id', $ids)->get();

        foreach ($offers as $offer) {
            app(DeleteRewardOfferAction::class)->execute($offer);
        }

        return $offers->count();
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'activate' => "{$result->affectedCount} پاداش با موفقیت فعال شدند.",
            'deactivate' => "{$result->affectedCount} پاداش با موفقیت غیرفعال شدند.",
            'delete' => "{$result->affectedCount} پاداش با موفقیت حذف شدند.",
            default => "{$result->affectedCount} مورد با موفقیت بروزرسانی شد.",
        };
    }
}
