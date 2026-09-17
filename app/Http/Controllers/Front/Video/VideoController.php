<?php

namespace App\Http\Controllers\Front\Video;

use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use App\Domain\Video\Scopes\ActiveVideoScope;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->resolveSort($request);
        $videos = $this->applySort(
            Video::query()->with(['cover.primaryVariant', 'categories', 'author'])->withCount('approvedComments'),
            $sort,
        )->paginate(12)->withQueryString();
        $featured = Video::query()
            ->featured()
            ->with(['cover.primaryVariant', 'categories', 'author'])
            ->withCount('approvedComments')
            ->latest('published_at')
            ->first();
        $categories = TaxonomyTerm::query()->forTaxonomy(TaxonomyKeyEnum::VIDEO_CATEGORY)
            ->whereNull('parent_id')->withCount('videos')->orderBy('title')->get();
        $trendingVideos = $this->trendingVideos();
        $totalVideos = Video::query()->count();

        request()->attributes->set('seo_paginator', $videos);

        return view('front.videos.index', compact('videos', 'featured', 'categories', 'trendingVideos', 'totalVideos', 'sort'));
    }

    public function show(string $videoSlug, ResolveSlugAction $resolveSlug, StoreViewAction $storeView): View|RedirectResponse
    {
        $resolved = $resolveSlug->execute(new ResolveSlugDTO(SlugScopeEnum::VIDEO, $videoSlug));
        abort_unless($resolved->found && $resolved->model instanceof Video, 404);
        if ($resolved->shouldRedirect) {
            return redirect()->to($resolved->redirectTo, 301);
        }

        /** @var Video $video */
        $video = $resolved->model;

        return $this->renderDetail($video, $storeView);
    }

    public function preview(int $video): View
    {
        Gate::authorize('video.list');

        $videoModel = Video::query()
            ->withoutGlobalScope(ActiveVideoScope::class)
            ->findOrFail($video);

        return $this->renderDetail($videoModel);
    }

    private function renderDetail(Video $video, ?StoreViewAction $storeView = null): View
    {
        $video->load([
            'cover.primaryVariant',
            'cover.variants',
            'author.avatar',
            'categories',
            'tags',
            'seoMeta',
        ])->loadCount('approvedComments');

        $storeView?->execute($video);

        $relatedVideos = $this->relatedVideos($video);
        $sidebarCategories = TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::VIDEO_CATEGORY)
            ->whereNull('parent_id')
            ->withCount('videos')
            ->orderByDesc('videos_count')
            ->orderBy('title')
            ->limit(6)
            ->get();

        request()->attributes->set('video', $video);

        return view('front.videos.detail', compact('video', 'relatedVideos', 'sidebarCategories'));
    }

    public function taxonomy(Request $request, string $path, string $taxonomy, ResolveSlugAction $resolveSlug): View|RedirectResponse
    {
        $key = $taxonomy === 'category' ? TaxonomyKeyEnum::VIDEO_CATEGORY : TaxonomyKeyEnum::VIDEO_TAG;
        $resolved = $resolveSlug->execute(new ResolveSlugDTO($key->slugScope(), $path));
        abort_unless($resolved->found && $resolved->model instanceof TaxonomyTerm, 404);
        if ($resolved->shouldRedirect) {
            return redirect()->to($resolved->redirectTo, 301);
        }

        /** @var TaxonomyTerm $term */
        $term = $resolved->model;
        $sort = $this->resolveSort($request);
        $videos = $this->applySort(
            $term->videosIncludingDescendants()->with(['cover.primaryVariant', 'categories', 'author'])->withCount('approvedComments'),
            $sort,
        )->paginate(12)->withQueryString();
        request()->attributes->set('video_taxonomy_term', $term);
        request()->attributes->set('seo_paginator', $videos);

        return view('front.videos.index', [
            'videos' => $videos,
            'featured' => null,
            'categories' => TaxonomyTerm::query()->forTaxonomy(TaxonomyKeyEnum::VIDEO_CATEGORY)->whereNull('parent_id')->withCount('videos')->orderBy('title')->get(),
            'term' => $term,
            'trendingVideos' => $this->trendingVideos(),
            'totalVideos' => Video::query()->count(),
            'sort' => $sort,
        ]);
    }

    private function resolveSort(Request $request): string
    {
        return in_array($request->query('sort'), ['latest', 'popular'], true)
            ? $request->query('sort')
            : 'latest';
    }

    private function applySort(Builder $query, string $sort): Builder
    {
        if ($sort !== 'popular') {
            return $query->latest('videos.published_at');
        }

        return $query
            ->leftJoin('page_views', function (JoinClause $join): void {
                $join->on('page_views.pageable_id', '=', 'videos.id')
                    ->where('page_views.pageable_type', Video::class);
            })
            ->select('videos.*')
            ->orderByRaw('COALESCE(page_views.count, 0) DESC')
            ->latest('videos.published_at');
    }

    private function trendingVideos(): Collection
    {
        return $this->applySort(
            Video::query()->with(['cover.primaryVariant', 'categories', 'author'])->withCount('approvedComments'),
            'popular',
        )->limit(4)->get();
    }

    private function relatedVideos(Video $video): Collection
    {
        $termIds = $video->categories->concat($video->tags)->pluck('id')->unique();
        $relations = [
            'cover.primaryVariant',
            'cover.variants',
            'categories',
            'author',
        ];

        $relatedVideos = Video::query()
            ->whereKeyNot($video->id)
            ->with($relations)
            ->withCount('approvedComments')
            ->when(
                $termIds->isNotEmpty(),
                fn (Builder $query) => $query->whereHas(
                    'taxonomyTerms',
                    fn (Builder $taxonomyQuery) => $taxonomyQuery->whereIn('taxonomy_terms.id', $termIds),
                ),
            )
            ->latest('published_at')
            ->limit(5)
            ->get();

        $remaining = 5 - $relatedVideos->count();

        if ($remaining <= 0) {
            return $relatedVideos;
        }

        $fallbackVideos = $this->applySort(
            Video::query()
                ->whereKeyNot($video->id)
                ->whereNotIn('videos.id', $relatedVideos->modelKeys())
                ->with($relations)
                ->withCount('approvedComments'),
            'popular',
        )->limit($remaining)->get();

        return $relatedVideos->concat($fallbackVideos);
    }
}
