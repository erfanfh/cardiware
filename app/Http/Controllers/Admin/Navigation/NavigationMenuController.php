<?php

namespace App\Http\Controllers\Admin\Navigation;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Enums\NavigationItemTargetEnum;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Navigation\Models\NavigationMenu;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Navigation\UpdateNavigationMenuRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class NavigationMenuController extends Controller
{
    public function show(
        NavigationMenu $navigationMenu,
    ): View
    {
        Gate::authorize('navigation-menu.manage');

        $navigationMenu->load([
            'rootItems.children.children',
        ]);

        $menuItems = $navigationMenu->rootItems
            ->map(fn(NavigationItem $item) => $this->mapNavigationItemForBuilder($item))
            ->values()
            ->toArray();

        $topCategories = TaxonomyTerm::query()
            ->categories()
            ->select(['id', 'title', 'path', 'slug'])
            ->orderBy('title')
            ->limit(8)
            ->get()
            ->map(fn(TaxonomyTerm $taxonomyTerm) => [
                'id' => (int)$taxonomyTerm->id,
                'title' => $taxonomyTerm->title,
            ])
            ->values();

        $topTags = TaxonomyTerm::query()
            ->tags()
            ->select(['id', 'title', 'path', 'slug'])
            ->orderBy('title')
            ->limit(8)
            ->get()
            ->map(fn(TaxonomyTerm $taxonomyTerm) => [
                'id' => (int)$taxonomyTerm->id,
                'title' => $taxonomyTerm->title,
            ])
            ->values();

        $topPosts = Post::query()
            ->select(['id', 'title', 'slug'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn(Post $post) => [
                'id' => (int)$post->id,
                'title' => $post->title,
            ])
            ->values();

        $topPages = Page::query()
            ->select(['id', 'title', 'slug'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn(Page $page) => [
                'id' => (int)$page->id,
                'title' => $page->title,
            ])
            ->values();

        $topProjects = Project::query()
            ->select(['id', 'title', 'slug'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn(Project $project) => [
                'id' => (int)$project->id,
                'title' => $project->title,
            ])
            ->values();

        return view('admin.navigation.menus.show', compact(
            'navigationMenu',
            'menuItems',
            'topCategories',
            'topTags',
            'topPosts',
            'topPages',
            'topProjects',
        ));
    }

    public function update(
        UpdateNavigationMenuRequest $request,
        NavigationMenu              $navigationMenu,
    ): RedirectResponse
    {
        Gate::authorize('navigation-menu.manage');

        $payload = $request->input('items_payload');
        $items = [];

        if (filled($payload)) {
            $items = json_decode($payload, true);

            if (!is_array($items)) {
                throw ValidationException::withMessages([
                    'items_payload' => 'ساختار آیتم‌های فهرست معتبر نیست.',
                ]);
            }
        }

        DB::transaction(function () use ($navigationMenu, $items) {
            $navigationMenu->items()->delete();

            $this->persistNavigationItems(
                menu: $navigationMenu,
                items: $items,
                parentId: null,
                depth: 0,
            );
        });

        return redirect()
            ->route('admin.navigation-menus.show', $navigationMenu)
            ->with('success', 'فهرست با موفقیت به‌روزرسانی شد.');
    }

    protected function mapNavigationItemForBuilder(NavigationItem $item): array
    {
        return [
            'id' => $item->id,
            'uid' => 'db-' . $item->id,
            'type' => $item->type->value,
            'typeLabel' => $item->type->label(),
            'title' => $item->title,
            'url' => $item->type === NavigationItemTypeEnum::CUSTOM_LINK ? $item->url : '',
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
            'children' => $item->children
                ->map(fn(NavigationItem $child) => $this->mapNavigationItemForBuilder($child))
                ->values()
                ->toArray(),
        ];
    }

    protected function persistNavigationItems(
        NavigationMenu $menu,
        array          $items,
        ?int           $parentId,
        int            $depth,
    ): void
    {
        foreach ($items as $sortOrder => $item) {
            $validatedItem = $this->validateNavigationItemNode($item);

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
                'mega_menu_columns' => $validatedItem['mega_menu_columns'],
                'mega_menu_style' => $validatedItem['mega_menu_style'],
                'sort_order' => $sortOrder,
                'depth' => $depth,
                'settings' => null,
            ]);

            $children = $validatedItem['children'] ?? [];

            if (!empty($children)) {
                $this->persistNavigationItems(
                    menu: $menu,
                    items: $children,
                    parentId: $navigationItem->id,
                    depth: $depth + 1,
                );
            }
        }
    }

    protected function validateNavigationItemNode(array $item): array
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
        $referenceType = $item['reference_type'] ?? null;
        $url = trim((string)($item['url'] ?? ''));

        if ($type === NavigationItemTypeEnum::CUSTOM_LINK->value) {
            if ($url === '') {
                throw ValidationException::withMessages([
                    'items_payload' => 'لینک یکی از آیتم‌های اختصاصی خالی است.',
                ]);
            }

            $referenceId = null;
            $referenceType = null;
        } else {
            if ($referenceId === null || $referenceType === null) {
                throw ValidationException::withMessages([
                    'items_payload' => 'مرجع یکی از آیتم‌های فهرست معتبر نیست.',
                ]);
            }

            $url = $this->resolveNavigationItemUrl(
                type: $type,
                referenceId: (int)$referenceId,
                referenceType: (string)$referenceType,
            );
        }

        return [
            'type' => $type,
            'title' => $title,
            'url' => $url,
            'target' => $target,
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
            'reference_type' => $referenceType ? (string)$referenceType : null,
            'icon' => $this->nullableTrimmedString($item['icon'] ?? null),
            'badge' => $this->nullableTrimmedString($item['badge'] ?? null),
            'css_class' => $this->nullableTrimmedString($item['css_class'] ?? null),
            'is_mega_menu' => (bool)($item['is_mega_menu'] ?? false),
            'mega_menu_title' => $this->nullableTrimmedString($item['mega_menu_title'] ?? null),
            'mega_menu_columns' => max(1, (int)($item['mega_menu_columns'] ?? 4)),
            'mega_menu_style' => $this->nullableTrimmedString($item['mega_menu_style'] ?? 'default') ?? 'default',
            'children' => is_array($item['children'] ?? null) ? $item['children'] : [],
        ];
    }

    protected function resolveNavigationItemUrl(
        string $type,
        int    $referenceId,
        string $referenceType,
    ): string
    {
        if (!class_exists($referenceType)) {
            throw ValidationException::withMessages([
                'items_payload' => 'کلاس مرجع یکی از آیتم‌های فهرست معتبر نیست.',
            ]);
        }

        /** @var class-string<Model> $referenceType */
        $model = $referenceType::query()->find($referenceId);

        if (!$model) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع یکی از آیتم‌های فهرست یافت نشد.',
            ]);
        }

        return match ($type) {
            NavigationItemTypeEnum::CATEGORY->value => $this->resolveCategoryModelUrl($model),
            NavigationItemTypeEnum::TAG->value => $this->resolveTagModelUrl($model),
            NavigationItemTypeEnum::POST->value => $this->resolvePostModelUrl($model),
            NavigationItemTypeEnum::PAGE->value => $this->resolvePageModelUrl($model),
            NavigationItemTypeEnum::PROJECT->value => $this->resolveProjectModelUrl($model),
            default => throw ValidationException::withMessages([
                'items_payload' => 'امکان ساخت لینک برای یکی از آیتم‌های فهرست وجود ندارد.',
            ]),
        };
    }

    protected function resolveCategoryModelUrl(Model $model): string
    {
        if (!$model instanceof TaxonomyTerm) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع دسته‌بندی معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->path ?: $model->slug, '/');
    }

    protected function resolveTagModelUrl(Model $model): string
    {
        if (!$model instanceof TaxonomyTerm) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع برچسب معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->path ?: $model->slug, '/');
    }

    protected function resolvePostModelUrl(Model $model): string
    {
        if (!$model instanceof Post) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع نوشته معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
    }

    protected function resolvePageModelUrl(Model $model): string
    {
        if (!$model instanceof Page) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع برگه معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
    }

    protected function resolveProjectModelUrl(Model $model): string
    {
        if (!$model instanceof Project) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع پروژه معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
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
