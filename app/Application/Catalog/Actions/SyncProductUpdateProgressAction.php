<?php

namespace App\Application\Catalog\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductUpdateProgress;

class SyncProductUpdateProgressAction
{
    public function execute(
        Product $product,
        array   $data
    ): ProductUpdateProgress
    {
        $progressPercentage = (int)(
            $data['progress_percentage'] ?? 0
        );

        $progressPercentage = max(
            0,
            min(100, $progressPercentage)
        );

        return $product->updateProgress()->updateOrCreate(
            [],
            [
                'is_visible' => filter_var(
                    $data['is_visible'] ?? false,
                    FILTER_VALIDATE_BOOL
                ),

                'title' => $this->normalizeText(
                    $data['title'] ?? null,
                    ProductUpdateProgress::DEFAULT_TITLE
                ),

                'description' => $this->normalizeText(
                    $data['description'] ?? null,
                    ProductUpdateProgress::DEFAULT_DESCRIPTION
                ),

                'progress_percentage' => $progressPercentage,

                /*
                 * فرض شده تاریخ‌ها در Request به میلادی تبدیل می‌شوند؛
                 * مشابه کاری که برای published_at محصول انجام می‌دهی.
                 */
                'started_at' => filled($data['started_at'] ?? null)
                    ? $data['started_at']
                    : null,

                'release_at' => filled($data['release_at'] ?? null)
                    ? $data['release_at']
                    : null,
            ]
        );
    }

    private function normalizeText(mixed $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }
}
