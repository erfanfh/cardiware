<?php

namespace App\Application\Navigation\Actions;

use App\Application\Navigation\Support\NavigationItemUrlResolver;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Enums\NavigationItemTargetEnum;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Navigation\Models\NavigationMenu;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncNavigationMenuItemsAction
{
    public function __construct(
        protected NavigationItemUrlResolver $urlResolver,
    )
    {
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function execute(NavigationMenu $menu, array $items): void
    {
        DB::transaction(function () use ($menu, $items) {
            $menu->items()->delete();

            $this->persistItems(
                menu: $menu,
                items: $items,
                parentId: null,
                depth: 0,
            );

            Cache::forget("navigation.frontend.{$menu->slug}");
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
                'settings' => null,
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
            'is_mega_menu' => (bool)($item['is_mega_menu'] ?? false),
            'mega_menu_title' => $this->nullableTrimmedString($item['mega_menu_title'] ?? null),
            'mega_menu_description' => $this->nullableTrimmedString($item['mega_menu_description'] ?? null),
            'mega_menu_columns' => max(1, (int)($item['mega_menu_columns'] ?? 4)),
            'children' => is_array($item['children'] ?? null) ? $item['children'] : [],
        ];
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
