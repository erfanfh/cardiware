<?php

namespace App\Http\Controllers\Admin\Navigation;

use App\Application\Navigation\Actions\SyncNavigationMenuItemsAction;
use App\Application\Navigation\Support\NavigationMenuTreeBuilder;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Models\NavigationMenu;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Navigation\UpdateNavigationMenuRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class NavigationMenuController extends Controller
{
    public function show(
        NavigationMenu            $navigationMenu,
        NavigationMenuTreeBuilder $treeBuilder,
    ): View
    {
        Gate::authorize('navigation-menu.manage');

        $menuItems = $treeBuilder->buildForAdmin(
            $navigationMenu->items()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
        );

        $topCategories = TaxonomyTerm::query()
            ->categories()
            ->select(['id', 'title'])
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
            ->select(['id', 'title'])
            ->orderBy('title')
            ->limit(8)
            ->get()
            ->map(fn(TaxonomyTerm $taxonomyTerm) => [
                'id' => (int)$taxonomyTerm->id,
                'title' => $taxonomyTerm->title,
            ])
            ->values();

        $topPosts = Post::query()
            ->select(['id', 'title'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn(Post $post) => [
                'id' => (int)$post->id,
                'title' => $post->title,
            ])
            ->values();

        $topPages = Page::query()
            ->select(['id', 'title'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn(Page $page) => [
                'id' => (int)$page->id,
                'title' => $page->title,
            ])
            ->values();

        $topProjects = Project::query()
            ->select(['id', 'title'])
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
        UpdateNavigationMenuRequest   $request,
        NavigationMenu                $navigationMenu,
        SyncNavigationMenuItemsAction $syncNavigationMenuItemsAction,
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

        $syncNavigationMenuItemsAction->execute(
            menu: $navigationMenu,
            items: $items,
        );

        return redirect()
            ->route('admin.navigation-menus.show', $navigationMenu)
            ->with('success', 'فهرست با موفقیت به‌روزرسانی شد.');
    }
}
