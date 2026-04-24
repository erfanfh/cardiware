<?php

namespace App\Application\Navigation\Actions;

use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ResolveFrontendNavigationMenuAction
{
    public function __construct(
        protected NavigationItemUrlResolver $urlResolver,
    )
    {
    }

    public function execute(string $slug): Collection
    {
        return Cache::rememberForever(
            "navigation.frontend.{$slug}",
            fn() => $this->resolve($slug)
        );
    }

    protected function resolve(string $slug): Collection
    {
        $menu = NavigationMenu::query()
            ->where('slug', $slug)
            ->first();

        if (!$menu) {
            return collect();
        }

        $items = NavigationItem::query()
            ->where('menu_id', $menu->id)
            ->with('reference')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->buildTree($items);
    }

    protected function buildTree(Collection $items, ?int $parentId = null): Collection
    {
        return $items
            ->where('parent_id', $parentId)
            ->values()
            ->map(fn(NavigationItem $item) => $this->mapItem($item, $items))
            ->filter()
            ->values();
    }

    protected function mapItem(NavigationItem $item, Collection $allItems): ?array
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
            'children' => $this->buildTree($allItems, $item->id),
        ];
    }

    public function forget(string $slug): void
    {
        Cache::forget("navigation.frontend.{$slug}");
    }
}
