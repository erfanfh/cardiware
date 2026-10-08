<?php

namespace App\Application\Navigation\Actions;

use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Application\Shared\Cache\Invalidators\NavigationCacheInvalidator;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Enums\NavigationItemTargetEnum;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Navigation\Models\NavigationMenu;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncNavigationMenuItemsAction
{
    public function __construct(
        protected NavigationItemUrlResolver  $urlResolver,
        protected NavigationCacheInvalidator $navigationCacheInvalidator,
    )
    {
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function execute(NavigationMenu $menu, array $items): void
    {
        DB::transaction(function () use ($menu, $items) {
            $menu->items()->get()->each->delete();

            $this->persistItems(
                menu: $menu,
                items: $items,
                parentId: null,
                depth: 0,
            );

            // The revision is committed with the menu changes. A frontend request that
            // started before this point can only repopulate its old revision's key.
            $menu->increment('cache_revision');

            DB::afterCommit(function () use ($menu) {
                $this->navigationCacheInvalidator->flushForMenu($menu);
            });
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    protected function persistItems(
        NavigationMenu $menu,
        array          $items,
        ?int           $parentId,
        int            $depth,
    ): void
    {
        foreach ($items as $sortOrder => $item) {
            $validatedItem = $this->validateNode($item);

            $navigationItem = NavigationItem::query()->create([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'type' => $validatedItem['type'],
                'title' => $validatedItem['title'],
                'url' => $validatedItem['url'],
                'target' => $validatedItem['target'],
                'reference_id' => $validatedItem['reference_id'],
                'reference_type' => $validatedItem['reference_type'],
                'icon' => $validatedItem['icon'],
                'badge' => $validatedItem['badge'],
                'css_class' => $validatedItem['css_class'],
                'is_mega_menu' => $validatedItem['is_mega_menu'],
                'mega_menu_title' => $validatedItem['mega_menu_title'],
                'mega_menu_description' => $validatedItem['mega_menu_description'],
                'mega_menu_columns' => $validatedItem['mega_menu_columns'],
                'sort_order' => $sortOrder,
                'depth' => $depth,
                'settings' => $validatedItem['mega_menu_quick_links'] === []
                    ? null
                    : ['mega_menu_quick_links' => $validatedItem['mega_menu_quick_links']],
            ]);

            $children = $validatedItem['children'] ?? [];

            if ($children !== []) {
                $this->persistItems(
                    menu: $menu,
                    items: $children,
                    parentId: $navigationItem->id,
                    depth: $depth + 1,
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    protected function validateNode(array $item): array
    {
        $type = (string)($item['type'] ?? '');
        $target = (string)($item['target'] ?? NavigationItemTargetEnum::SELF->value);

        if (!in_array($type, NavigationItemTypeEnum::values(), true)) {
            throw ValidationException::withMessages([
                'items_payload' => 'نوع یکی از آیتم‌های فهرست معتبر نیست.',
            ]);
        }

        if (!in_array($target, NavigationItemTargetEnum::values(), true)) {
            throw ValidationException::withMessages([
                'items_payload' => 'مقدار target یکی از آیتم‌های فهرست معتبر نیست.',
            ]);
        }

        $title = trim((string)($item['title'] ?? ''));

        if ($title === '') {
            throw ValidationException::withMessages([
                'items_payload' => 'عنوان یکی از آیتم‌های فهرست خالی است.',
            ]);
        }

        $referenceId = $item['reference_id'] ?? null;
        $referenceType = null;
        $url = null;

        if ($type === NavigationItemTypeEnum::CUSTOM_LINK->value) {
            $url = trim((string)($item['url'] ?? ''));
            $referenceId = null;
        } else {
            if ($referenceId === null) {
                throw ValidationException::withMessages([
                    'items_payload' => 'مرجع یکی از آیتم‌های فهرست معتبر نیست.',
                ]);
            }

            $referenceType = $this->resolveReferenceTypeByItemType($type);

            if ($referenceType === null) {
                throw ValidationException::withMessages([
                    'items_payload' => 'نوع مرجع یکی از آیتم‌های فهرست معتبر نیست.',
                ]);
            }

            $this->urlResolver->assertResolvable(
                type: $type,
                referenceId: (int)$referenceId,
                referenceType: $referenceType,
            );
        }

        $isMegaMenu = (bool)($item['is_mega_menu'] ?? false);
        $megaMenuQuickLinks = $isMegaMenu
            ? $this->validateMegaMenuQuickLinks($item['mega_menu_quick_links'] ?? [])
            : [];

        return [
            'type' => $type,
            'title' => $title,
            'url' => $url,
            'target' => $target,
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
            'reference_type' => $referenceType,
            'icon' => $this->nullableTrimmedString($item['icon'] ?? null),
            'badge' => $this->nullableTrimmedString($item['badge'] ?? null),
            'css_class' => $this->nullableTrimmedString($item['css_class'] ?? null),
            'is_mega_menu' => $isMegaMenu,
            'mega_menu_title' => $this->nullableTrimmedString($item['mega_menu_title'] ?? null),
            'mega_menu_description' => $this->nullableTrimmedString($item['mega_menu_description'] ?? null),
            'mega_menu_columns' => max(1, (int)($item['mega_menu_columns'] ?? 4)),
            'mega_menu_quick_links' => $megaMenuQuickLinks,
            'children' => is_array($item['children'] ?? null) ? $item['children'] : [],
        ];
    }

    /**
     * @return array<int, array{title: string, url: string, target: string}>
     */
    protected function validateMegaMenuQuickLinks(mixed $links): array
    {
        if (!is_array($links)) {
            throw ValidationException::withMessages([
                'items_payload' => 'ساختار لینک‌های دسترسی سریع معتبر نیست.',
            ]);
        }

        if (count($links) > 12) {
            throw ValidationException::withMessages([
                'items_payload' => 'برای هر مگامنو حداکثر ۱۲ لینک دسترسی سریع قابل ثبت است.',
            ]);
        }

        $normalized = [];

        foreach ($links as $link) {
            if (!is_array($link)) {
                throw ValidationException::withMessages([
                    'items_payload' => 'ساختار یکی از لینک‌های دسترسی سریع معتبر نیست.',
                ]);
            }

            $title = trim((string)($link['title'] ?? ''));
            $url = trim((string)($link['url'] ?? ''));
            $target = (string)($link['target'] ?? NavigationItemTargetEnum::SELF->value);

            if ($title === '' && $url === '') {
                continue;
            }

            if ($title === '' || mb_strlen($title) > 120) {
                throw ValidationException::withMessages([
                    'items_payload' => 'عنوان لینک دسترسی سریع الزامی است و نباید بیشتر از ۱۲۰ کاراکتر باشد.',
                ]);
            }

            if ($url === '' || mb_strlen($url) > 2048 || !$this->isSafeQuickLinkUrl($url)) {
                throw ValidationException::withMessages([
                    'items_payload' => 'آدرس یکی از لینک‌های دسترسی سریع معتبر نیست.',
                ]);
            }

            if (!in_array($target, NavigationItemTargetEnum::values(), true)) {
                throw ValidationException::withMessages([
                    'items_payload' => 'نحوه باز شدن یکی از لینک‌های دسترسی سریع معتبر نیست.',
                ]);
            }

            $normalized[] = compact('title', 'url', 'target');
        }

        return $normalized;
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

    protected function resolveReferenceTypeByItemType(string $type): ?string
    {
        return match ($type) {
            NavigationItemTypeEnum::CATEGORY->value => TaxonomyTerm::class,
            NavigationItemTypeEnum::TAG->value => TaxonomyTerm::class,
            NavigationItemTypeEnum::POST->value => Post::class,
            NavigationItemTypeEnum::PAGE->value => Page::class,
            NavigationItemTypeEnum::PROJECT->value => Project::class,
            default => null,
        };
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }
}
