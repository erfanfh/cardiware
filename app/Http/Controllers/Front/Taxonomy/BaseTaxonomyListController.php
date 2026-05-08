<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

abstract class BaseTaxonomyListController extends Controller
{
    public function __invoke(
        string            $path,
        ResolveSlugAction $resolveSlugAction,
        ?int              $page = null,
    ): View|RedirectResponse
    {
        $page = max(1, (int)($page ?? request()->route('page', 1)));

        $resolvedSlug = $resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: $this->slugScope(),
                value: $path,
            )
        );

        if (!$resolvedSlug->found) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var TaxonomyTerm $term */
        $term = $resolvedSlug->model;

        if ($page === 1 && request()->routeIs($this->paginatedRouteName())) {
            return redirect()->to($this->baseUrl($term), 301);
        }

        $postsQuery = $term->isHierarchical()
            ? $term->postsIncludingDescendants()
            : $term->posts();

        $posts = $postsQuery
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'categories',
            ])
            ->latest('published_at')
            ->paginate(
                perPage: get_setting('general', 'select_category_tag_posts_count_settings') ?? 9,
                pageName: 'page',
                page: $page,
            );

        if ($page > 1 && $posts->lastPage() > 0 && $page > $posts->lastPage()) {
            abort(404);
        }

        request()->attributes->set('seo_paginator', $posts);
        request()->attributes->set('seo_pagination_base_url', $this->baseUrl($term));

        return view($this->viewName(), [
            'posts' => $posts,
            'term' => $term,
            $this->termViewVariableName() => $term,
        ]);
    }

    abstract protected function slugScope(): SlugScopeEnum;

    abstract protected function routeName(): string;

    abstract protected function paginatedRouteName(): string;

    abstract protected function termViewVariableName(): string;

    protected function viewName(): string
    {
        return 'front.posts.list';
    }

    protected function baseUrl(TaxonomyTerm $term): string
    {
        return front_route($this->routeName(), [
            'path' => $term->path,
        ]);
    }
}
