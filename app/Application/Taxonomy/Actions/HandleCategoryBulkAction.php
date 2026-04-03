<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Infrastructure\Support\BulkActions\BulkActionResultDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleCategoryBulkAction
{
    /**
     * @param string $action
     * @param array<int|string> $ids
     * @return BulkActionResultDTO
     * @throws Throwable
     */
    public function execute(string $action, array $ids): BulkActionResultDTO
    {
        $ids = $this->normalizeIds($ids);

        if (empty($ids)) {
            return new BulkActionResultDTO(
                action: $action,
                affectedCount: 0,
            );
        }

        return DB::transaction(function () use ($action, $ids) {
            $affectedCount = match ($action) {
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
     * @param array<int|string> $ids
     * @return list<int>
     */
    protected function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param list<int> $ids
     * @return int
     */
    protected function delete(array $ids): int
    {
        $terms = TaxonomyTerm::categories()
            ->whereIn('id', $ids)
            ->get();

        $count = $terms->count();

        foreach ($terms as $term) {
            $term->delete();
        }

        return $count;
    }

    public function successMessage(BulkActionResultDTO $result): string
    {
        return match ($result->action) {
            'delete' => "{$result->affectedCount} برچسب با موفقیت حذف شد.",
            default => "{$result->affectedCount} مورد با موفقیت به‌روزرسانی شد.",
        };
    }
}
