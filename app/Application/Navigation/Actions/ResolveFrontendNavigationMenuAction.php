<?php

namespace App\Application\Navigation\Actions;

use App\Application\Icon\Actions\ResolveIconSvgAction;
use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ResolveFrontendNavigationMenuAction
{
    public function __construct(
        protected NavigationItemUrlResolver $urlResolver,
        protected ResolveIconSvgAction      $resolveIconSvgAction,
    )
    {
    }

    public function execute(string $slug): Collection
    {
        $menu = $this->findMenu($slug);

        if (!$menu) {
            return collect();
        }

        return Cache::tags([
            CacheTag::NAVIGATION,
            CacheTag::navigationMenu($slug),
        ])->rememberForever(
            CacheKey::frontendNavigation($slug, $menu->cache_revision),
            fn() => $this->resolve($menu->id)
        );
    }

    protected function findMenu(string $slug): ?NavigationMenu
    {
        return NavigationMenu::query()
            ->select(['id', 'cache_revision'])
            ->where('slug', $slug)
            ->first();
    }

    protected function resolve(int $menuId): Collection
    {
        $items = NavigationItem::query()
            ->where('menu_id', $menuId)
            ->with('reference')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $iconSvgs = $this->resolveIconSvgAction->executeMany(
            $items->pluck('icon')->filter()->all(),
        );

        return $this->buildTree($items, iconSvgs: $iconSvgs);
    }

    protected function buildTree(
        Collection $items,
        ?int       $parentId = null,
        array      $iconSvgs = [],
    ): Collection
    {
        return $items
            ->where('parent_id', $parentId)
            ->values()
            ->map(fn(NavigationItem $item) => $this->mapItem($item, $items, $iconSvgs))
            ->filter()
            ->values();
    }

    protected function mapItem(
        NavigationItem $item,
        Collection     $allItems,
        array          $iconSvgs,
    ): ?array
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
            'icon_svg' => $iconSvgs[$item->icon] ?? null,
            'badge' => $item->badge,
            'css_class' => $item->css_class,
            'is_mega_menu' => $item->is_mega_menu,
            'mega_menu_title' => $item->mega_menu_title,
            'mega_menu_description' => $item->mega_menu_description,
            'mega_menu_columns' => $item->mega_menu_columns,
            'mega_menu_quick_links' => $this->resolveMegaMenuQuickLinks($item),
            'children' => $this->buildTree($allItems, $item->id, $iconSvgs),
        ];
    }

    /**
     * @return array<int, array{title: string, url: string, target: string}>
     */
    protected function resolveMegaMenuQuickLinks(NavigationItem $item): array
    {
        if (!$item->is_mega_menu) {
            return [];
        }

        return collect(data_get($item->settings, 'mega_menu_quick_links', []))
            ->filter(fn(mixed $link): bool => is_array($link))
            ->map(function (array $link): array {
                $target = (string)($link['target'] ?? '_self');

                return [
                    'title' => trim((string)($link['title'] ?? '')),
                    'url' => trim((string)($link['url'] ?? '')),
                    'target' => in_array($target, ['_self', '_blank'], true) ? $target : '_self',
                ];
            })
            ->filter(fn(array $link): bool => $link['title'] !== '' && $this->isSafeQuickLinkUrl($link['url']))
            ->take(12)
            ->values()
            ->all();
    }

    protected function isSafeQuickLinkUrl(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        if (str_starts_with($url, '#') && !preg_match('/[\s\x00-\x1F]/u', $url)) {
            return true;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }

        return in_array($scheme, ['mailto', 'tel'], true)
            && !preg_match('/[\s\x00-\x1F]/u', $url);
    }
}
