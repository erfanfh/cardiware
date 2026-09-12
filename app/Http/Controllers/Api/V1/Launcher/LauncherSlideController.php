<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Launcher\Models\LauncherSlide;
use App\Http\Controllers\Controller;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class LauncherSlideController extends Controller
{
    public function __invoke(): JsonResponse
    {
        // Global content; flushed by LauncherCacheObserver on any slide change. The
        // visibility window (starts_at/ends_at) is time-based, hence the short TTL.
        $slides = Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_PORTAL])->remember(
            CacheKey::launcherSlides(),
            now()->addMinutes(5),
            fn(): array => $this->buildSlides(),
        );

        return response()->json(['data' => $slides]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSlides(): array
    {
        $slides = LauncherSlide::query()
            ->visible()
            ->with(['image.primaryVariant', 'targetSaleItem:id,launcher_enabled'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(static fn(LauncherSlide $slide): bool => filled($slide->image?->url))
            ->map(static function (LauncherSlide $slide): array {
                $targetType = $slide->target_type;
                if ($targetType === 'product'
                    && $slide->targetSaleItem?->launcher_enabled !== true) {
                    $targetType = 'none';
                }

                return [
                    'id' => $slide->id,
                    'title' => $slide->title,
                    'description' => $slide->description,
                    'button_text' => $slide->button_text,
                    'image_url' => $slide->image?->url,
                    'target_type' => $targetType,
                    'target_sale_item_id' => $targetType === 'product'
                        ? $slide->target_sale_item_id
                        : null,
                    'target_url' => $targetType === 'url'
                        ? $slide->target_url
                        : null,
                    'sort_order' => (int)$slide->sort_order,
                ];
            })
            ->values();

        return $slides->all();
    }
}
