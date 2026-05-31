<?php

namespace App\Application\Catalog\Actions\ProductAttribute;

use App\Domain\Catalog\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteSelectedProductAttributesAction
{
    public function __construct(
        private readonly DeleteProductAttributeAction $deleteProductAttributeAction,
    )
    {
    }

    /**
     * @param array<int, int> $ids
     * @return int
     *
     * @throws Throwable
     */
    public function execute(array $ids): int
    {
        $ids = collect($ids)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return 0;
        }

        $usedCount = ProductAttribute::query()
            ->whereIn('id', $ids)
            ->whereHas('assignments')
            ->count();

        if ($usedCount > 0) {
            throw ValidationException::withMessages([
                'bulkAction' => 'برخی ویژگی‌های انتخاب‌شده در محصولات استفاده شده‌اند و قابل حذف نیستند.',
            ]);
        }

        return DB::transaction(function () use ($ids): int {
            $attributes = ProductAttribute::query()
                ->whereIn('id', $ids)
                ->get();

            foreach ($attributes as $attribute) {
                $this->deleteProductAttributeAction->execute($attribute);
            }

            return $attributes->count();
        });
    }
}
