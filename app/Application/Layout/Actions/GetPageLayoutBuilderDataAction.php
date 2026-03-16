<?php

namespace App\Application\Layout\Actions;

use App\Application\Layout\Support\PageLayoutElementRegistry;
use App\Domain\Layout\Models\PageLayout;

class GetPageLayoutBuilderDataAction
{
    public function __construct(
        private readonly PageLayoutElementRegistry $registry,
    )
    {
    }

    /**
     * Get builder-ready data for the given page layout key.
     *
     * @return array<string, mixed>
     */
    public function execute(PageLayout $layout): array
    {
        $layout = $layout->load('rows.areas.elements');

        return [
            'layout' => [
                'id' => $layout->id,
                'key' => $layout->key,
                'title' => $layout->title,
            ],
            'rows' => $layout->rows
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'key' => $row->key,
                        'layout_type' => $row->layout_type,
                        'sort_order' => $row->sort_order,
                        'areas' => $row->areas
                            ->map(function ($area) {
                                return [
                                    'id' => $area->id,
                                    'key' => $area->key,
                                    'title' => $area->title,
                                    'description' => $area->description,
                                    'width' => $area->width,
                                    'sort_order' => $area->sort_order,
                                    'elements' => $area->elements
                                        ->map(function ($element) {
                                            $definition = $this->registry->get($element->key);

                                            return [
                                                'id' => $element->id,
                                                'key' => $element->key,
                                                'type' => $element->type,
                                                'type_label' => $definition['type_label'] ?? $element->title,
                                                'type_color' => $definition['type_color'] ?? 'blue',
                                                'title' => $element->title,
                                                'description' => $element->description,
                                                'is_active' => $element->is_active,
                                                'sort_order' => $element->sort_order,
                                                'allowed_areas' => $definition['allowed_areas'] ?? [],
                                                'settings_url' => $this->resolveSettingsUrl($definition),
                                            ];
                                        })
                                        ->values()
                                        ->all(),
                                ];
                            })
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @param array|null $definition
     * @return string|null
     */
    private function resolveSettingsUrl(?array $definition): ?string
    {
        if (!$definition || empty($definition['settings_route'])) {
            return null;
        }

        return route($definition['settings_route']);
    }
}
