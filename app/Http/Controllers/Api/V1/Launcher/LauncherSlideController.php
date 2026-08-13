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
            ->filter(static fn(LauncherSlide $slide): bool =>
                $slide->target_type !== 'product'
                || $slide->targetSaleItem?->launcher_enabled === true)
            ->map(static fn(LauncherSlide $slide): array => [
                'id' => $slide->id,
                'title' => $slide->title,
                'description' => $slide->description,
                'button_text' => $slide->button_text,
                'image_url' => $slide->image?->url,
                'target_type' => $slide->target_type,
                'target_sale_item_id' => $slide->target_type === 'product'
                    ? $slide->target_sale_item_id
                    : null,
                'target_url' => $slide->target_type === 'url'
                    ? $slide->target_url
                    : null,
                'sort_order' => (int)$slide->sort_order,
            ])
            ->values();

        return response()->json(['data' => $slides]);
    }
}
