<?php

namespace App\Http\Controllers\Front\Search;

use App\Application\Search\Support\ContentSearchQuery;
use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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

        [$postsCount, $projectsCount, $productsCount, $videosCount] = app(PublicReadCache::class)->remember(
            'search-counts', [$searchTerm], [Post::class, Project::class, Product::class, Video::class],
            fn () => [
                (clone $postsQuery)->reorder()->count(),
                (clone $projectsQuery)->reorder()->count(),
                (clone $productsQuery)->reorder()->count(),
                (clone $videosQuery)->reorder()->count(),
            ],
            $this->publicationTransitions(),
        );

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
            $query = (clone $postsQuery)
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'categories',
                ])
                ->latest();
            $posts = $this->cachedResults($query, $searchTerm, $type, $postsCount);

            request()->attributes->set('seo_paginator', $posts);
        }

        if ($type === 'projects') {
            $query = (clone $projectsQuery)->latest();
            $projects = $this->cachedResults($query, $searchTerm, $type, $projectsCount);

            request()->attributes->set('seo_paginator', $projects);
        }

        if ($type === 'products') {
            $query = (clone $productsQuery)
                ->with([
                    'picture.primaryVariant',
                    'picture.variants',
                    'store',
                    'categories',
                    'saleItems.discount',
                ])
                ->latest();
            $products = $this->cachedResults($query, $searchTerm, $type, $productsCount);

            request()->attributes->set('seo_paginator', $products);
        }

        if ($type === 'videos') {
            $query = (clone $videosQuery)
                ->with([
                    'cover.primaryVariant',
                    'cover.variants',
                    'categories',
                    'author',
                ])
                ->withCount('approvedComments')
                ->latest('published_at');
            $videos = $this->cachedResults($query, $searchTerm, $type, $videosCount);

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

    private function publicationTransitions(): array
    {
        return [Post::class => ['published_at'], Product::class => ['published_at'], Video::class => ['published_at']];
    }

    private function cachedResults(Builder $query, string $searchTerm, string $type, int $total): LengthAwarePaginator
    {
        $perPage = max(1, (int) (get_setting('general', 'search_'.$type.'_count_settings') ?? 12));
        $page = max(1, Paginator::resolveCurrentPage());

        return app(PublicReadCache::class)->paginate(
            'search-results', [$searchTerm, $type, $page, $perPage],
            [
                $query->getModel()::class, MediaFile::class, TaxonomyTerm::class,
                Store::class, ProductSaleItem::class, ProductSaleItemDiscount::class,
                User::class, Comment::class,
            ],
            fn () => $query->paginate($perPage, ['*'], 'page', $page, $total),
            $this->publicationTransitions(),
        )->withQueryString();
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
