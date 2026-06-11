<?php

namespace App\Application\Catalog\Actions\ProductInformationField;

use App\Domain\Catalog\Models\ProductInformationField;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteSelectedProductInformationFieldsAction
{
    /**
     * @param array<int, int> $ids
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

        $usedCount = ProductInformationField::query()
            ->whereIn('id', $ids)
            ->whereHas('entries')
            ->count();

        if ($usedCount > 0) {
            throw ValidationException::withMessages([
                'fields' => 'برخی از فیلدهای انتخاب‌شده در اطلاعات محصولات استفاده شده‌اند و قابل حذف نیستند.',
            ]);
        }

        return DB::transaction(function () use ($ids) {
            return ProductInformationField::query()
                ->whereIn('id', $ids)
                ->delete();
        });
    }
}
