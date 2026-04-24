<?php

namespace App\Application\Navigation\Actions;

use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Support\Collection;

class ResolveFrontendNavigationMenuAction
{
    public function __construct(
        protected NavigationItemUrlResolver $urlResolver,
    )
    {
    }

    public function execute(string $slug): Collection
    {
        $menu = NavigationMenu::query()
            ->where('slug', $slug)
            ->with([
                'items' => fn($query) => $query
                    ->whereNull('parent_id')
                    ->with(['children', 'reference'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->first();

        if (!$menu) {
            return collect();
        }

        return $menu->items
            ->map(fn($item) => $this->mapItem($item))
            ->filter()
            ->values();
    }

    protected function mapItem($item): ?array
    {
        $url = $this->urlResolver->resolveForItem($item);

        if (!$url) {
            return null;
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'url' => $url,
            'target' => $item->target->value,
            'icon' => $item->icon,
            'badge' => $item->badge,
            'css_class' => $item->css_class,
            'is_mega_menu' => $item->is_mega_menu,
            'mega_menu_title' => $item->mega_menu_title,
            'mega_menu_description' => $item->mega_menu_description,
            'mega_menu_columns' => $item->mega_menu_columns,
            'children' => $item->children
                ->map(fn($child) => $this->mapItem($child))
                ->filter()
                ->values(),
        ];
    }
}
