<?php

namespace App\Application\Layout\Actions;

use App\Application\Layout\Support\PageLayoutFrontRegistry;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Layout\Models\PageLayout;
use Illuminate\Support\Facades\Cache;

class GetPageLayoutRenderDataAction
{
    public function __construct(
        private readonly PageLayoutFrontRegistry $frontRegistry,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $pageLayoutKey): array
    {
        return Cache::tags([
            CacheTag::PAGE_LAYOUTS,
            CacheTag::PAGE_LAYOUT_RENDER,
            CacheTag::pageLayout($pageLayoutKey),
        ])->remember(
            CacheKey::pageLayoutRender($pageLayoutKey),
            CacheTtl::long(),
            function () use ($pageLayoutKey): array {
                $pageLayout = PageLayout::query()
                    ->where('key', $pageLayoutKey)
                    ->with([
                        'rows.areas.elements' => fn($query) => $query
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ])
                    ->first();

                $rows = [];

                foreach ($pageLayout->rows ?? [] as $row) {
                    $renderedAreas = [];

                    foreach ($row->areas as $area) {
                        $renderedElements = [];

                        foreach ($area->elements as $element) {
                            $definition = $this->frontRegistry->get($element->key);

                            if (!$definition || empty($definition['view'])) {
                                continue;
                            }

                            $renderedElements[] = [
                                'id' => $element->id,
                                'key' => $element->key,
                                'type' => $element->type,
                                'title' => $element->title,
                                'description' => $element->description,
                                'view' => $definition['view'],
                            ];
                        }

                        if (empty($renderedElements)) {
                            continue;
                        }

                        $renderedAreas[] = [
                            'id' => $area->id,
                            'key' => $area->key,
                            'title' => $area->title,
                            'description' => $area->description,
                            'width' => $area->width ?: 12,
                            'elements' => $renderedElements,
                        ];
                    }

                    if (empty($renderedAreas)) {
                        continue;
                    }

                    $rows[] = [
                        'id' => $row->id,
                        'key' => $row->key,
                        'layout_type' => $row->layout_type,
                        'areas' => $renderedAreas,
                    ];
                }

                return [
                    // چیدمان هنوز ساخته نشده (نصب تازه): صفحه خالی به‌جای خطای ۵۰۰.
                    'layout' => [
                        'id' => $pageLayout?->id,
                        'key' => $pageLayout?->key ?? $pageLayoutKey,
                        'title' => $pageLayout?->title,
                    ],
                    'rows' => $rows,
                ];
            }
        );
    }
}
