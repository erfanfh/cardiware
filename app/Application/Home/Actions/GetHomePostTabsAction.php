<?php

namespace App\Application\Home\Actions;

use Illuminate\Support\Collection;

class GetHomePostTabsAction
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function execute(): Collection
    {
        return collect(get_setting('home', 'posts_items') ?? [])
            ->map(function (array $item, int $index) {
                return [
                    'key' => 'tab-' . ($index + 1),

                    'main_title' => $item['main_title'] ?? '',
                    'main_count' => max(1, (int)($item['main_count'] ?? 10)),
                    'main_category_ids' => collect($item['main_category_ids'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->values()
                        ->all(),

                    'list_box_1_title' => $item['list_box_1_title'] ?? '',
                    'list_box_1_count' => max(1, (int)($item['list_box_1_count'] ?? 10)),
                    'list_box_1_category_ids' => collect($item['list_box_1_category_ids'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->values()
                        ->all(),

                    'list_box_2_title' => $item['list_box_2_title'] ?? '',
                    'list_box_2_count' => max(1, (int)($item['list_box_2_count'] ?? 10)),
                    'list_box_2_category_ids' => collect($item['list_box_2_category_ids'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->values()
                        ->all(),

                    'sort_order' => (int)($item['sort_order'] ?? 0),
                ];
            })
            ->filter(fn(array $item) => filled($item['main_title']))
            ->sortBy('sort_order')
            ->values();
    }
}
