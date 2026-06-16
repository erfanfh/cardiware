<?php

namespace App\Http\Controllers\Front\Search;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        $searchTerm = trim((string)$request->q);

        $hasExplicitType = $request->query->has('type');

        $requestedType = $request->query('type', 'posts');

        $allowedTypes = [
            'posts',
            'projects',
            'products',
        ];

        if (!in_array($requestedType, $allowedTypes, true)) {
            return redirect()->route('search', [
                'q' => $searchTerm,
            ]);
        }

        $postsQuery = $this->postsQuery($searchTerm);
        $projectsQuery = $this->projectsQuery($searchTerm);
        $productsQuery = $this->productsQuery($searchTerm);

        $postsCount = (clone $postsQuery)->count();
        $projectsCount = (clone $projectsQuery)->count();
        $productsCount = (clone $productsQuery)->count();

        $type = $requestedType;

        if (
            !$hasExplicitType
            && $this->countByType($type, $postsCount, $projectsCount, $productsCount) === 0
        ) {
            $type = $this->resolveFirstAvailableType(
                postsCount: $postsCount,
                projectsCount: $projectsCount,
                productsCount: $productsCount,
            );
        }

        $posts = null;
        $projects = null;
        $products = null;

        if ($type === 'posts') {
            $posts = (clone $postsQuery)
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'categories',
                ])
                ->latest()
                ->paginate((int)(get_setting('general', 'search_posts_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $posts);
        }

        if ($type === 'projects') {
            $projects = (clone $projectsQuery)
                ->latest()
                ->paginate((int)(get_setting('general', 'search_projects_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $projects);
        }

        if ($type === 'products') {
            $products = (clone $productsQuery)
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'store',
                ])
                ->latest()
                ->paginate((int)(get_setting('general', 'search_products_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $products);
        }

        return view('front.search', compact(
            'posts',
            'projects',
            'products',
            'type',
            'postsCount',
            'projectsCount',
            'productsCount',
            'searchTerm',
        ));
    }

    private function postsQuery(string $searchTerm): Builder
    {
        return Post::query()
            ->where('title', 'LIKE', '%' . $searchTerm . '%');
    }

    private function projectsQuery(string $searchTerm): Builder
    {
        return Project::query()
            ->where('title', 'LIKE', '%' . $searchTerm . '%');
    }

    private function productsQuery(string $searchTerm): Builder
    {
        return Product::query()
            ->where('title', 'LIKE', '%' . $searchTerm . '%');
    }

    private function countByType(
        string $type,
        int    $postsCount,
        int    $projectsCount,
        int    $productsCount,
    ): int
    {
        return match ($type) {
            'posts' => $postsCount,
            'products' => $productsCount,
            'projects' => $projectsCount,
            default => 0,
        };
    }

    private function resolveFirstAvailableType(
        int $postsCount,
        int $projectsCount,
        int $productsCount,
    ): string
    {
        return match (true) {
            $postsCount > 0 => 'posts',
            $productsCount > 0 => 'products',
            $projectsCount > 0 => 'projects',
            default => 'posts',
        };
    }
}
