<?php

namespace App\Application\Navigation\Support;

use App\Domain\Navigation\Models\NavigationItem;
use Illuminate\Support\Collection;

class NavigationMenuTreeBuilder
{
    /**
     * Build nested tree for admin builder from flat items collection.
     *
     * @param Collection<int, NavigationItem> $items
     * @return array<int, array<string, mixed>>
     */
    public function buildForAdmin(Collection $items): array
    {
        $groupedByParent = $items
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->groupBy(fn(NavigationItem $item) => $item->parent_id ?? 0);

        return $this->buildChildren(
            groupedByParent: $groupedByParent,
            parentId: 0,
        );
    }

    /**
     * @param Collection<int|string, Collection<int, NavigationItem>> $groupedByParent
     * @return array<int, array<string, mixed>>
     */
    protected function buildChildren(Collection $groupedByParent, int $parentId): array
    {
        /** @var Collection<int, NavigationItem> $items */
        $items = $groupedByParent->get($parentId, collect());

        return $items
            ->map(function (NavigationItem $item) use ($groupedByParent): array {
                return [
                    'id' => $item->id,
                    'uid' => 'db-' . $item->id,
                    'type' => $item->type->value,
                    'typeLabel' => $item->type->label(),
                    'title' => $item->title,
                    'url' => $item->type->isCustomLink() ? $item->url : '',
                    'referenceId' => $item->reference_id,
                    'referenceType' => $item->reference_type,
                    'isOpen' => false,
                    'target' => $item->target->value,
                    'icon' => $item->icon,
                    'badge' => $item->badge,
                    'cssClass' => $item->css_class,
                    'isMegaMenu' => $item->is_mega_menu,
                    'megaMenuTitle' => $item->mega_menu_title,
                    'megaMenuColumns' => (string)$item->mega_menu_columns,
                    'megaMenuStyle' => $item->mega_menu_style ?? 'default',
                    'children' => $this->buildChildren(
                        groupedByParent: $groupedByParent,
                        parentId: $item->id,
                    ),
                ];
            })
            ->values()
            ->all();
    }
}
