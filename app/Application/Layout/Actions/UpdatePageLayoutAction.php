<?php

namespace App\Application\Layout\Actions;

use App\Application\Layout\Support\PageLayoutElementRegistry;
use App\Application\Shared\Cache\Invalidators\PageLayoutCacheInvalidator;
use App\Domain\Layout\Models\PageLayout;
use App\Domain\Layout\Models\PageLayoutArea;
use App\Domain\Layout\Models\PageLayoutElement;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class UpdatePageLayoutAction
{
    public function __construct(
        private readonly PageLayoutElementRegistry  $registry,
        private readonly PageLayoutCacheInvalidator $pageLayoutCacheInvalidator,
    )
    {
    }

    /**
     * @param PageLayout $pageLayout
     * @param array $layoutState
     * @param array $elementStates
     * @return void
     * @throws Throwable
     */
    public function execute(PageLayout $pageLayout, array $layoutState, array $elementStates = []): void
    {
        DB::transaction(function () use ($pageLayout, $layoutState, $elementStates): void {
            $pageLayout->load([
                'rows.areas.elements',
            ]);

            $areas = PageLayoutArea::query()
                ->whereIn(
                    'id',
                    $pageLayout->rows
                        ->flatMap(fn($row) => $row->areas)
                        ->pluck('id')
                        ->all()
                )
                ->get()
                ->keyBy('key');

            $elements = PageLayoutElement::query()
                ->whereIn(
                    'id',
                    $pageLayout->rows
                        ->flatMap(fn($row) => $row->areas)
                        ->flatMap(fn($area) => $area->elements)
                        ->pluck('id')
                        ->all()
                )
                ->get()
                ->keyBy('key');

            $this->syncElementPositions(
                layoutState: $layoutState,
                areas: $areas->all(),
                elements: $elements->all(),
            );

            $this->syncElementActivationStates(
                elementStates: $elementStates,
                elements: $elements->all(),
            );
        });

        $this->pageLayoutCacheInvalidator->flushForLayout($pageLayout);
    }

    /**
     * @param array $layoutState
     * @param array $areas
     * @param array $elements
     * @return void
     */
    private function syncElementPositions(array $layoutState, array $areas, array $elements): void
    {
        foreach ($layoutState as $areaKey => $elementKeys) {
            if (!isset($areas[$areaKey]) || !is_array($elementKeys)) {
                continue;
            }

            $targetArea = $areas[$areaKey];

            foreach (array_values($elementKeys) as $index => $elementKey) {
                if (!is_string($elementKey) || !isset($elements[$elementKey])) {
                    continue;
                }

                $element = $elements[$elementKey];

                $this->guardAreaAssignment($element->key, $areaKey);

                $element->update([
                    'page_layout_area_id' => $targetArea->id,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }

    /**
     * @param array $elementStates
     * @param array $elements
     * @return void
     */
    private function syncElementActivationStates(array $elementStates, array $elements): void
    {
        foreach ($elements as $elementKey => $element) {
            $isActive = !empty($elementStates[$elementKey]['is_active']);

            $element->update([
                'is_active' => $isActive,
            ]);
        }
    }

    /**
     * @param string $elementKey
     * @param string $targetAreaKey
     * @return void
     */
    private function guardAreaAssignment(string $elementKey, string $targetAreaKey): void
    {
        $definition = $this->registry->get($elementKey);

        if (!$definition) {
            return;
        }

        $allowedAreas = $definition['allowed_areas'] ?? [];

        if (empty($allowedAreas)) {
            return;
        }

        if (!in_array($targetAreaKey, $allowedAreas, true)) {
            throw new RuntimeException("Element [{$elementKey}] cannot be moved to area [{$targetAreaKey}].");
        }
    }
}
