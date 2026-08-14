<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Launcher\Models\LauncherSlide;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class LauncherSlideController extends Controller
{
    public function __invoke(): JsonResponse
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

        return response()->json(['data' => $slides]);
    }
}
