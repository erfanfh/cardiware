<?php

namespace App\Http\Controllers\Front\Search;

use App\Application\Search\Support\ContentSearchQuery;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Domain\Video\Models\Video;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly ContentSearchQuery $contentSearchQuery,
    ) {}

    public function index(Request $request): RedirectResponse|View
    {
        $searchTerm = $this->normalizeSearchTerm((string) $request->query('q', ''));

        $hasExplicitType = $request->query->has('type');

        $requestedType = $request->query('type', 'posts');

        $allowedTypes = [
            'posts',
            'projects',
            'products',
            'videos',
        ];

        if (! in_array($requestedType, $allowedTypes, true)) {
            return redirect()->route('search', [
                'q' => $searchTerm,
            ]);
        }

        $postsQuery = $this->postsQuery($searchTerm);
        $projectsQuery = $this->projectsQuery($searchTerm);
        $productsQuery = $this->productsQuery($searchTerm);
        $videosQuery = $this->videosQuery($searchTerm);

        $postsCount = (clone $postsQuery)->reorder()->count();
        $projectsCount = (clone $projectsQuery)->reorder()->count();
        $productsCount = (clone $productsQuery)->reorder()->count();
        $videosCount = (clone $videosQuery)->reorder()->count();

        $type = $requestedType;

        if (
            ! $hasExplicitType
            && $this->countByType($type, $postsCount, $projectsCount, $productsCount, $videosCount) === 0
        ) {
            $type = $this->resolveFirstAvailableType(
                postsCount: $postsCount,
                projectsCount: $projectsCount,
                productsCount: $productsCount,
                videosCount: $videosCount,
            );
        }

        $posts = null;
        $projects = null;
        $products = null;
        $videos = null;

        if ($type === 'posts') {
            $posts = (clone $postsQuery)
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'categories',
                ])
                ->latest()
                ->paginate((int) (get_setting('general', 'search_posts_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $posts);
        }

        if ($type === 'projects') {
            $projects = (clone $projectsQuery)
                ->latest()
                ->paginate((int) (get_setting('general', 'search_projects_count_settings') ?? 12))
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
                ->paginate((int) (get_setting('general', 'search_products_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $products);
        }

        if ($type === 'videos') {
            $videos = (clone $videosQuery)
                ->with([
                    'cover.primaryVariant',
                    'cover.variants',
                    'categories',
                    'author',
                ])
                ->withCount('approvedComments')
                ->latest('published_at')
                ->paginate((int) (get_setting('general', 'search_videos_count_settings') ?? 12))
                ->withQueryString();

            request()->attributes->set('seo_paginator', $videos);
        }

        return view('front.search', compact(
            'posts',
            'projects',
            'products',
            'videos',
            'type',
            'postsCount',
            'projectsCount',
            'productsCount',
            'videosCount',
            'searchTerm',
        ));
    }

    private function postsQuery(string $searchTerm): Builder
    {
        return $this->contentSearchQuery->apply(Post::query(), $searchTerm);
    }

    private function projectsQuery(string $searchTerm): Builder
    {
        return $this->contentSearchQuery->apply(Project::query(), $searchTerm);
    }

    private function productsQuery(string $searchTerm): Builder
    {
        return $this->contentSearchQuery->apply(Product::query(), $searchTerm);
    }

    private function videosQuery(string $searchTerm): Builder
    {
        return $this->contentSearchQuery->apply(Video::query(), $searchTerm);
    }

    private function normalizeSearchTerm(string $searchTerm): string
    {
        $searchTerm = trim((string) preg_replace('/\s+/u', ' ', $searchTerm));

        return mb_substr($searchTerm, 0, 120);
    }

    private function countByType(
        string $type,
        int $postsCount,
        int $projectsCount,
        int $productsCount,
        int $videosCount,
    ): int {
        return match ($type) {
            'posts' => $postsCount,
            'products' => $productsCount,
            'projects' => $projectsCount,
            'videos' => $videosCount,
            default => 0,
        };
    }

    private function resolveFirstAvailableType(
        int $postsCount,
        int $projectsCount,
        int $productsCount,
        int $videosCount,
    ): string {
        return match (true) {
            $postsCount > 0 => 'posts',
            $productsCount > 0 => 'products',
            $projectsCount > 0 => 'projects',
            $videosCount > 0 => 'videos',
            default => 'posts',
        };
    }
}
