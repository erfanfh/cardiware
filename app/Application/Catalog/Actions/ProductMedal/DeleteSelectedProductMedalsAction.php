<?php

namespace App\Application\Catalog\Actions\ProductMedal;

use App\Domain\Catalog\Models\ProductMedal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteSelectedProductMedalsAction
{
    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function execute(array $ids): int
    {
        $ids = collect($ids)
            ->map(fn($id) => (int)$id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $medals = ProductMedal::query()
            ->whereKey($ids->all())
            ->withCount('assignments')
            ->get();

        $usedMedals = $medals
            ->filter(fn(ProductMedal $medal) => $medal->assignments_count > 0)
            ->values();

        if ($usedMedals->isNotEmpty()) {
            $titles = $usedMedals
                ->take(3)
                ->pluck('title')
                ->implode('، ');

            $remainingCount = max(0, $usedMedals->count() - 3);

            $message = "مدال‌های {$titles} در محصولات استفاده شده‌اند و قابل حذف نیستند.";

            if ($remainingCount > 0) {
                $message .= " همچنین {$remainingCount} مدال استفاده‌شده دیگر نیز انتخاب شده است.";
            }

            throw ValidationException::withMessages([
                'selectedIds' => $message,
            ]);
        }

        return DB::transaction(
            fn(): int => ProductMedal::query()
                ->whereKey($medals->modelKeys())
                ->delete()
        );
    }
}
